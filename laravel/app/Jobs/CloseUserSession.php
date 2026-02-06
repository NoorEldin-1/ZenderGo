<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SessionManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to close a user's WhatsApp session after campaign completion.
 * This job is dispatched with a delay to ensure messages are fully sent.
 */
class CloseUserSession implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Closing WhatsApp session for user {$this->userId}");

        $user = User::find($this->userId);

        if (!$user) {
            Log::warning("User {$this->userId} not found for session close");
            return;
        }

        $sessionManager = new SessionManager();

        // Check if there are any pending jobs for this user's session
        // If so, skip closing to avoid interrupting ongoing campaigns
        $hasPendingJobs = $this->hasPendingCampaignJobs($user);

        if ($hasPendingJobs) {
            Log::info("User {$this->userId} has pending campaign jobs, deferring session close");
            // Re-dispatch with another delay
            self::dispatch($this->userId)->delay(now()->addMinutes(2));
            return;
        }

        $success = $sessionManager->closeSession($user);

        if ($success) {
            Log::info("Successfully closed session for user {$this->userId}");
        } else {
            Log::warning("Failed to close session for user {$this->userId}");
        }
    }

    /**
     * Check if user has pending campaign jobs.
     */
    protected function hasPendingCampaignJobs(User $user): bool
    {
        // Check the jobs table for pending jobs with this user's session
        $pendingCount = \DB::table('jobs')
            ->where('payload', 'like', '%"whatsappSession":"' . $user->whatsapp_session . '"%')
            ->count();

        return $pendingCount > 0;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to close session for user {$this->userId}: " . $exception->getMessage());
    }
}
