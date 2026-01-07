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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWhatsappCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * Reduced for faster failover on 8GB VPS
     */
    public int $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     * Reduced for faster recovery
     */
    public int $backoff = 30;

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

        // CRITICAL FIX: First try lightweight check to see if session is ALREADY active
        // This prevents race conditions when multiple jobs call wakeSession simultaneously
        if ($user) {
            // Try lightweight check first (no start attempt)
            $activeCheck = $sessionManager->isSessionActive($user);

            if ($activeCheck['active'] && $activeCheck['service']) {
                // Session already active - just use it! No need to restart.
                Log::info("Session already active for user {$this->userId} - reusing");
                $whatsapp = $activeCheck['service'];
            } else {
                // Session not tracked as active - do full wake (this should only happen for first job in batch)
                Log::info("Session not active for user {$this->userId}, performing full wake");
                $wakeResult = $sessionManager->wakeSession($user);

                if ($wakeResult['status'] === 'needs_qr') {
                    // User disconnected from phone - they need to reconnect
                    Log::warning("User {$this->userId} needs to re-scan QR code");
                    $this->handleDisconnection($user, $wakeResult['message'] ?? 'يجب إعادة مسح QR Code');
                    return;
                }

                if ($wakeResult['status'] !== 'connected' || !$wakeResult['service']) {
                    Log::error("Failed to wake session for user {$this->userId}: {$wakeResult['message']}");
                    $this->handleDisconnection($user, $wakeResult['message'] ?? 'فشل في الاتصال');
                    return;
                }

                $whatsapp = $wakeResult['service'];
            }

            // Quick connection verification (not the heavy deep check for every message)
            $connectionStatus = $whatsapp->checkConnection();
            if (!($connectionStatus['connected'] ?? false)) {
                Log::warning("Connection lost during campaign for user {$this->userId}");
                $this->handleDisconnection($user, 'تم فقدان الاتصال أثناء الإرسال');
                return;
            }
        } else {
            // Fallback to legacy behavior if userId not provided
            $whatsapp = new WhatsAppService($this->whatsappSession, $this->whatsappToken);

            // Pre-flight deep connection check
            $connectionStatus = $whatsapp->deepConnectionCheck();
            if (!($connectionStatus['connected'] ?? false)) {
                Log::warning("WhatsApp session {$this->whatsappSession} is disconnected");
                $this->fail(new WhatsAppDisconnectedException(
                    $connectionStatus['message'] ?? 'جلسة WhatsApp غير متصلة',
                    $this->whatsappSession
                ));
                return;
            }
        }

        // Normalize image path and check if file exists
        $normalizedImagePath = $this->imagePath ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->imagePath) : null;

        Log::debug("Image path received: {$this->imagePath}");
        Log::debug("Normalized image path: {$normalizedImagePath}");
        Log::debug("File exists check: " . ($normalizedImagePath && file_exists($normalizedImagePath) ? 'true' : 'false'));

        // Send message with verification
        $sendResult = null;

        if ($normalizedImagePath && file_exists($normalizedImagePath)) {
            // Send image with caption (collage or single image)
            Log::info("Sending image with campaign to {$this->phone}");
            $sendResult = $whatsapp->sendImageWithVerification($this->phone, $this->message, $normalizedImagePath);
        } else {
            // Send text message only
            Log::info("No image found, sending text only to {$this->phone}");
            $sendResult = $whatsapp->sendMessageWithVerification($this->phone, $this->message);
        }

        // Check send result
        if (!($sendResult['success'] ?? false)) {
            $reason = $sendResult['reason'] ?? 'unknown';
            $needsReauth = $sendResult['needs_reauth'] ?? false;

            Log::warning("Failed to send campaign to {$this->phone}", [
                'reason' => $reason,
                'message' => $sendResult['message'] ?? 'Unknown error',
            ]);

            // If disconnection detected during send, handle it
            if ($needsReauth && $user) {
                $this->handleDisconnection($user, $sendResult['message'] ?? 'تم قطع الاتصال');
                return;
            }

            $this->fail(new \Exception("Failed to send message to {$this->phone}: " . ($sendResult['message'] ?? 'Unknown error')));
            return;
        }

        Log::info("Successfully sent campaign to {$this->phone}");

        // Update session activity
        if ($user) {
            $sessionManager->markSessionActive($user->id, $this->whatsappSession);
        }

        // If this is the last message in the batch, clean up resources
        if ($this->isLastInBatch && $user) {
            // Cleanup collage image after batch completion (saves storage)
            if ($normalizedImagePath && file_exists($normalizedImagePath) && str_contains(basename($normalizedImagePath), 'collage_')) {
                Log::info("Cleaning up collage image: {$normalizedImagePath}");
                @unlink($normalizedImagePath);
            }

            // CRITICAL: Close session after batch completion to free RAM
            // This prevents RAM accumulation with 50-70 concurrent users on KVM 2 VPS
            Log::info("Last message in batch sent for user {$user->id}, closing session to free RAM");
            $sessionManager->closeSession($user);
        }
    }

    /**
     * Handle disconnection: mark session invalid and cancel pending jobs for this user.
     */
    protected function handleDisconnection(User $user, string $message): void
    {
        Log::warning("Handling disconnection for user {$user->id}: {$message}");

        // Update user session state
        $user->update([
            'session_state' => 'disconnected',
        ]);

        // Cancel all pending jobs for this user
        $this->cancelPendingJobsForUser($user->id);

        // Fail this job
        $this->fail(new WhatsAppDisconnectedException(
            $message,
            $this->whatsappSession
        ));
    }

    /**
     * Cancel all pending campaign jobs for a specific user.
     */
    protected function cancelPendingJobsForUser(int $userId): void
    {
        try {
            // Delete pending jobs from the queue that belong to this user
            $deletedCount = DB::table('jobs')
                ->where('payload', 'LIKE', '%"userId":' . $userId . '%')
                ->orWhere('payload', 'LIKE', '%"userId":' . $userId . ',%')
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Cancelled {$deletedCount} pending jobs for user {$userId} due to disconnection");
            }
        } catch (\Exception $e) {
            Log::error("Failed to cancel pending jobs for user {$userId}: " . $e->getMessage());
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

        // If it's a disconnection error, update user session state
        if ($exception instanceof WhatsAppDisconnectedException && $this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                $user->update(['session_state' => 'disconnected']);
                Log::warning("Marked user {$this->userId} session as disconnected");
            }
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

