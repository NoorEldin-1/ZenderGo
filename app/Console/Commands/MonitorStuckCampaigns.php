<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorStuckCampaigns extends Command
{
    protected $signature = 'campaigns:monitor-stuck
        {--minutes=30 : Minutes of inactivity before marking as stuck}';

    protected $description = 'Detect and resolve campaigns stuck in processing state';

    public function handle(): int
    {
        $minutesThreshold = (int) $this->option('minutes');

        $stuckCampaigns = Campaign::whereIn('status', Campaign::ACTIVE_STATUSES)
            ->where('updated_at', '<', now()->subMinutes($minutesThreshold))
            ->get();

        if ($stuckCampaigns->isEmpty()) {
            $this->info('No stuck campaigns found.');
            return self::SUCCESS;
        }

        foreach ($stuckCampaigns as $campaign) {
            $minutesStuck = now()->diffInMinutes($campaign->updated_at);

            $reason = "توقفت الحملة تلقائياً بسبب عدم النشاط لمدة {$minutesStuck} دقيقة";

            $campaign->markFailed($reason);

            Log::warning("Stuck campaign detected and resolved", [
                'campaign_id' => $campaign->id,
                'user_id' => $campaign->user_id,
                'sent' => $campaign->sent_count,
                'total' => $campaign->total_contacts,
                'minutes_stuck' => $minutesStuck,
            ]);

            $this->warn("Campaign #{$campaign->id} (User #{$campaign->user_id}): Stuck for {$minutesStuck}m → marked FAILED");
        }

        $this->info("Resolved {$stuckCampaigns->count()} stuck campaign(s).");

        return self::SUCCESS;
    }
}
