<?php

namespace App\Jobs;

use App\Exceptions\WhatsAppDisconnectedException;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        public ?string $whatsappToken = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Sending WhatsApp campaign to {$this->phone} using session {$this->whatsappSession}");

        // Create WhatsApp service with the user's session and token
        $whatsapp = new WhatsAppService($this->whatsappSession, $this->whatsappToken);

        // Pre-flight connection check to fail fast if disconnected
        try {
            $connectionStatus = $whatsapp->checkConnection();

            if (!($connectionStatus['connected'] ?? false)) {
                Log::warning("WhatsApp session {$this->whatsappSession} is disconnected, cannot send message to {$this->phone}");

                // Don't retry if session is disconnected - it won't reconnect automatically
                $this->fail(new WhatsAppDisconnectedException(
                    'جلسة WhatsApp غير متصلة',
                    $this->whatsappSession
                ));
                return;
            }
        } catch (\Exception $e) {
            Log::warning("Failed to check connection status for {$this->whatsappSession}: " . $e->getMessage());
            // Continue anyway - the actual send will fail if there's a real problem
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
            // The middleware cache will be updated on next status check
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
