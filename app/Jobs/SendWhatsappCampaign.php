<?php

namespace App\Jobs;

use App\Exceptions\WhatsAppDisconnectedException;
use App\Models\User;
use App\Services\SessionManager;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendWhatsappCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phone,
        public string $message,
        public ?string $imagePath = null,
        public ?string $whatsappSession = null,
        public ?string $whatsappToken = null,
        public ?int $userId = null,
        public bool $isLastInBatch = false
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Sending WhatsApp campaign to {$this->phone} using session {$this->whatsappSession}");

        $sessionManager = new SessionManager();

        // Get user for session management
        $user = $this->userId ? User::find($this->userId) : null;

        // Wake session on-demand
        if ($user) {
            $wakeResult = $sessionManager->wakeSession($user);

            if ($wakeResult['status'] === 'needs_qr') {
                // User disconnected from phone - they need to reconnect
                Log::warning("User {$this->userId} needs to re-scan QR code");
                $this->fail(new WhatsAppDisconnectedException(
                    $wakeResult['message'],
                    $this->whatsappSession
                ));
                return;
            }

            if ($wakeResult['status'] !== 'connected' || !$wakeResult['service']) {
                Log::error("Failed to wake session for user {$this->userId}: {$wakeResult['message']}");
                $this->fail(new WhatsAppDisconnectedException(
                    $wakeResult['message'],
                    $this->whatsappSession
                ));
                return;
            }

            $whatsapp = $wakeResult['service'];
        } else {
            // Fallback to legacy behavior if userId not provided
            $whatsapp = new WhatsAppService($this->whatsappSession, $this->whatsappToken);

            // Pre-flight connection check
            try {
                $connectionStatus = $whatsapp->checkConnection();
                if (!($connectionStatus['connected'] ?? false)) {
                    Log::warning("WhatsApp session {$this->whatsappSession} is disconnected");
                    $this->fail(new WhatsAppDisconnectedException(
                        'جلسة WhatsApp غير متصلة',
                        $this->whatsappSession
                    ));
                    return;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to check connection: " . $e->getMessage());
            }
        }

        $success = false;

        // Normalize image path and check if file exists
        $normalizedImagePath = $this->imagePath ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->imagePath) : null;

        Log::debug("Image path received: {$this->imagePath}");
        Log::debug("Normalized image path: {$normalizedImagePath}");
        Log::debug("File exists check: " . ($normalizedImagePath && file_exists($normalizedImagePath) ? 'true' : 'false'));

        if ($normalizedImagePath && file_exists($normalizedImagePath)) {
            // Send image with caption (collage or single image)
            Log::info("Sending image with campaign to {$this->phone}");
            $success = $whatsapp->sendImage($this->phone, $this->message, $normalizedImagePath);
        } else {
            // Send text message only
            Log::info("No image found, sending text only to {$this->phone}");
            $success = $whatsapp->sendMessage($this->phone, $this->message);
        }

        if (!$success) {
            Log::warning("Failed to send campaign to {$this->phone}");
            $this->fail(new \Exception("Failed to send message to {$this->phone}"));
            return;
        }

        Log::info("Successfully sent campaign to {$this->phone}");

        // Update session activity
        if ($user) {
            $sessionManager->markSessionActive($user->id, $this->whatsappSession);
        }

        // If this is the last message in the batch, close the session to free RAM
        if ($this->isLastInBatch && $user) {
            Log::info("Last message in batch, scheduling session close for user {$user->id}");
            // Delay close by 30 seconds to ensure message is fully sent
            CloseUserSession::dispatch($user->id)->delay(now()->addSeconds(30));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Campaign job failed for {$this->phone}: " . $exception->getMessage(), [
            'session' => $this->whatsappSession,
            'exception_class' => get_class($exception),
        ]);

        // If it's a disconnection error, we might want to mark the user's session as invalid
        if ($exception instanceof WhatsAppDisconnectedException) {
            Log::warning("WhatsApp disconnection detected during campaign send to {$this->phone}");
        }
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function shouldRetry(\Throwable $exception): bool
    {
        // Don't retry disconnection errors - session won't reconnect automatically
        if ($exception instanceof WhatsAppDisconnectedException) {
            return false;
        }

        return $this->attempts() < $this->tries;
    }
}

