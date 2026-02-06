<?php

namespace App\Console\Commands;

use App\Services\SessionManager;
use Illuminate\Console\Command;

/**
 * Command to close idle WhatsApp sessions.
 * Should be scheduled to run every 5 minutes.
 */
class CloseIdleSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sessions:cleanup 
                            {--force : Force close all sessions regardless of activity}
                            {--dry-run : Show which sessions would be closed without actually closing them}';

    /**
     * The console command description.
     */
    protected $description = 'Close idle WhatsApp sessions to free RAM';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sessionManager = new SessionManager();

        $this->info('Checking for idle sessions...');

        $activeSessions = $sessionManager->getActiveSessions();
        $idleSessions = $sessionManager->getIdleSessions();

        $this->line("Active sessions: " . count($activeSessions));
        $this->line("Idle sessions: " . count($idleSessions));

        if ($this->option('force')) {
            $this->warn('Force mode enabled - will close ALL sessions');
            $idleSessions = $activeSessions;
        }

        if (empty($idleSessions)) {
            $this->info('No idle sessions to close.');
            return Command::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run - would close the following sessions:');
            foreach ($idleSessions as $userId => $data) {
                $this->line(" - User {$userId}: {$data['session_name']}");
            }
            return Command::SUCCESS;
        }

        $closed = $sessionManager->closeIdleSessions();

        $this->info("Closed {$closed} idle sessions.");

        // Show RAM status
        $ramStatus = $sessionManager->getRamStatus();
        $this->line('');
        $this->info('RAM Status:');
        $this->line(" Total: {$ramStatus['total_mb']} MB");
        $this->line(" Used: {$ramStatus['used_mb']} MB ({$ramStatus['usage_percent']}%)");
        $this->line(" Free: {$ramStatus['free_mb']} MB");

        if ($ramStatus['is_critical']) {
            $this->error('⚠️  RAM usage is CRITICAL!');
        } elseif ($ramStatus['is_warning']) {
            $this->warn('⚠️  RAM usage is high');
        }

        return Command::SUCCESS;
    }
}
