<?php

namespace App\Jobs;

use App\Exceptions\WhatsAppDisconnectedException;
use App\Models\Campaign;
use App\Models\Contact;
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
     * Anti-ban delay range in seconds (min, max).
     * A random value in this range is used between messages.
     */
    private const DELAY_MIN_SECONDS = 15;
    private const DELAY_MAX_SECONDS = 45;

    /**
     * Typing simulation time range in milliseconds (min, max).
     * Shows "typing..." indicator to the recipient before sending.
     */
    private const TYPING_MIN_MS = 3000;
    private const TYPING_MAX_MS = 8000;

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
        public bool $isLastInBatch = false,
        public ?int $campaignId = null
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

        if (!$user) {
            // Legacy fallback - process without session management
            $this->processLegacyMessage();
            return;
        }

        // ====== SEQUENTIAL PROCESSING VIA BLOCKING LOCK ======
        // Per-user campaign lock - blocks until previous job completes for instant sequential sending
        // Lock timeout increased to accommodate anti-ban delays (up to 120s per message)
        $lock = Cache::lock("campaign_job_lock:{$this->userId}", 120);

        // Block for up to 90 seconds waiting for lock (instead of releasing to queue)
        // Increased to accommodate anti-ban random delays between messages
        if (!$lock->block(90)) {
            Log::warning("Failed to acquire lock after 90s for user {$this->userId}, releasing to queue");
            $this->release(2); // Try again in 2 seconds as fallback
            return;
        }

        // ====== PRE-FLIGHT: Check if campaign is still active ======
        // If a previous job marked the campaign as failed/cancelled,
        // abort immediately instead of sending more messages.
        $campaign = $this->campaignId ? Campaign::find($this->campaignId) : null;
        if ($campaign && $campaign->isFinished()) {
            Log::info("Campaign {$campaign->id} already finished (status: {$campaign->status}), skipping message to {$this->phone}");
            $lock->forceRelease();
            return;
        }

        try {
            // Check if our session is already active
            $activeCheck = $sessionManager->isSessionActive($user);

            if (!$activeCheck['active']) {
                // Our session is not running - check if we can start one
                $currentCount = $sessionManager->getActiveSessionCount();

                if ($currentCount >= SessionManager::MAX_CONCURRENT_SESSIONS) {
                    Log::info("Session limit reached ({$currentCount}/3), releasing job for user {$this->userId}");
                    $lock->release();
                    $this->release(30); // Wait 30 seconds for a slot
                    return;
                }

                // We have a slot available - do full wake
                Log::info("Session not active for user {$this->userId}, performing full wake");
                $wakeResult = $sessionManager->wakeSession($user, 'job');

                if ($wakeResult['status'] === 'needs_qr') {
                    // User disconnected from phone - they need to reconnect
                    Log::warning("User {$this->userId} needs to re-scan QR code");
                    $lock->release();
                    $this->handleDisconnection($user, $wakeResult['message'] ?? 'يجب إعادة مسح QR Code');
                    return;
                }

                if ($wakeResult['status'] !== 'connected' || !$wakeResult['service']) {
                    Log::error("Failed to wake session for user {$this->userId}: {$wakeResult['message']}");
                    $lock->release();
                    $this->handleDisconnection($user, $wakeResult['message'] ?? 'فشل في الاتصال');
                    return;
                }

                $whatsapp = $wakeResult['service'];
            } else {
                // Session already active - just use it! No need to restart.
                Log::info("Session already active for user {$this->userId} - reusing");
                $whatsapp = $activeCheck['service'];
            }

            // Quick connection verification
            $connectionStatus = $whatsapp->checkConnection();
            if (!($connectionStatus['connected'] ?? false)) {
                Log::warning("Connection lost during campaign for user {$this->userId}");
                $lock->release();
                $this->handleDisconnection($user, 'تم فقدان الاتصال أثناء الإرسال');
                return;
            }

            // ====== ANTI-BAN: RANDOM DELAY BETWEEN MESSAGES ======
            // Sleep for a random duration to simulate human pacing
            $delay = rand(self::DELAY_MIN_SECONDS, self::DELAY_MAX_SECONDS);
            Log::info("Anti-ban delay: sleeping {$delay}s before sending to {$this->phone}");
            sleep($delay);

            // ====== SEND MESSAGE ======
            $this->sendMessage($whatsapp, $sessionManager, $user);

        } finally {
            // Release the lock - safe to call even if we released early
            $lock->forceRelease();
        }
    }

    /**
     * Legacy message processing without session management.
     */
    protected function processLegacyMessage(): void
    {
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

        $this->sendMessageCore($whatsapp);
    }

    /**
     * Send message with session management.
     */
    protected function sendMessage(WhatsAppService $whatsapp, SessionManager $sessionManager, User $user): void
    {
        $this->sendMessageCore($whatsapp);

        // ====== UPDATE LAST CONTACTED TIMESTAMP ======
        // Record the successful send time for the contact
        if ($this->userId && $this->phone) {
            Contact::where('user_id', $this->userId)
                ->where('phone', $this->phone)
                ->update(['last_sent_at' => now()]);
            Log::debug("Updated last_sent_at for contact {$this->phone} (user {$this->userId})");
        }

        // ====== DB-BACKED PROGRESS UPDATE ======
        $campaign = $this->campaignId ? Campaign::find($this->campaignId) : null;
        if ($campaign && $campaign->isActive()) {
            $campaign->recordSuccess();
            Log::debug("Campaign {$campaign->id} progress: {$campaign->sent_count}/{$campaign->total_contacts}");
        }

        // Update session activity
        $sessionManager->markSessionActive($user->id, $this->whatsappSession);

        // If this is the last message in the batch, clean up resources
        if ($this->isLastInBatch) {
            $this->cleanupAfterBatch($sessionManager, $user);
        }
    }

    /**
     * Core message sending logic.
     */
    protected function sendMessageCore(WhatsAppService $whatsapp): void
    {
        // Generate random typing time (anti-ban measure)
        $typingTime = rand(self::TYPING_MIN_MS, self::TYPING_MAX_MS);
        Log::info("Anti-ban: typing simulation {$typingTime}ms for {$this->phone}");

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
            $sendResult = $whatsapp->sendImageWithVerification($this->phone, $this->message, $normalizedImagePath, $typingTime);
        } else {
            // Send text message only
            Log::info("No image found, sending text only to {$this->phone}");
            $sendResult = $whatsapp->sendMessageWithVerification($this->phone, $this->message, $typingTime);
        }

        // Check send result
        if (!($sendResult['success'] ?? false)) {
            $reason = $sendResult['reason'] ?? 'unknown';

            Log::warning("Failed to send campaign to {$this->phone}", [
                'reason' => $reason,
                'message' => $sendResult['message'] ?? 'Unknown error',
            ]);

            throw new \Exception("Failed to send message to {$this->phone}: " . ($sendResult['message'] ?? 'Unknown error'));
        }

        Log::info("Successfully sent campaign to {$this->phone}");
    }

    /**
     * Cleanup after batch completion.
     */
    protected function cleanupAfterBatch(SessionManager $sessionManager, User $user): void
    {
        // Normalize image path for cleanup check
        $normalizedImagePath = $this->imagePath ? str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->imagePath) : null;

        // Cleanup collage image after batch completion (saves storage)
        if ($normalizedImagePath && file_exists($normalizedImagePath) && str_contains(basename($normalizedImagePath), 'collage_')) {
            Log::info("Cleaning up collage image: {$normalizedImagePath}");
            @unlink($normalizedImagePath);
        }

        // CRITICAL: Close session after batch completion to free RAM
        // This prevents RAM accumulation with 50-70 concurrent users on KVM 2 VPS
        Log::info("Last message in batch sent for user {$user->id}, closing session to free RAM");
        $sessionManager->closeSession($user);

        // ====== DB-BACKED: Ensure campaign is marked complete ======
        // The Campaign::recordSuccess() already handles completion detection,
        // but we do a final safety check here for the last-in-batch edge case.
        $campaign = $this->campaignId ? Campaign::find($this->campaignId) : null;
        if ($campaign && $campaign->isActive()) {
            $campaign->update(['status' => Campaign::STATUS_COMPLETED]);
            Log::info("Campaign {$campaign->id} marked completed (batch cleanup)");
        }

        Log::info("Campaign batch completed for user {$user->id}");
    }

    /**
     * Handle disconnection: mark session and campaign as failed, cancel pending jobs.
     */
    protected function handleDisconnection(User $user, string $message): void
    {
        Log::warning("Handling disconnection for user {$user->id}: {$message}");

        // Update user session state
        $user->update([
            'session_state' => 'disconnected',
        ]);

        // ====== DB-BACKED: Mark campaign as failed immediately ======
        // This instantly stops the UI spinner on the next poll cycle.
        $campaign = $this->campaignId ? Campaign::find($this->campaignId) : null;
        if ($campaign && $campaign->isActive()) {
            $campaign->markFailed($message);
            Log::info("Campaign {$campaign->id} marked as FAILED due to disconnection");
        }

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

        // ====== DB-BACKED: Record failure in campaign ======
        $campaign = $this->campaignId ? Campaign::find($this->campaignId) : null;
        if ($campaign && $campaign->isActive()) {
            if ($exception instanceof WhatsAppDisconnectedException) {
                // Disconnection = entire campaign fails
                $campaign->markFailed($exception->getMessage());
            } else {
                // Individual message failure = record it, campaign may continue
                $campaign->recordFailure();
            }
        }

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

