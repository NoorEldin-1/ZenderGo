<?php

namespace App\Console\Commands;

use App\Services\SessionManager;
use Illuminate\Console\Command;

/**
 * Command to check RAM usage and close sessions if critical.
 * Should be scheduled to run every minute for proactive monitoring.
 */
class CheckRamUsage extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ram:check 
                            {--auto-cleanup : Automatically close sessions if RAM is critical}';

    /**
     * The console command description.
     */
    protected $description = 'Check server RAM usage and optionally close sessions if critical';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sessionManager = new SessionManager();
        $ramStatus = $sessionManager->getRamStatus();

        $this->info('=== RAM Status Report ===');
        $this->line('');
        $this->line(" Total Memory: {$ramStatus['total_mb']} MB");
        $this->line(" Used Memory:  {$ramStatus['used_mb']} MB");
        $this->line(" Free Memory:  {$ramStatus['free_mb']} MB");
        $this->line(" Usage:        {$ramStatus['usage_percent']}%");
        $this->line('');

        // Show active sessions
        $activeSessions = $sessionManager->getActiveSessions();
        $this->line("Active WhatsApp Sessions: " . count($activeSessions));

        if ($ramStatus['is_critical']) {
            $this->error('🚨 RAM CRITICAL - Server may crash soon!');

            if ($this->option('auto-cleanup')) {
                $this->warn('Auto-cleanup enabled - closing sessions...');

                // Close idle sessions first
                $closed = $sessionManager->closeIdleSessions();
                $this->line("Closed {$closed} idle sessions");

                // If still critical, force close oldest active session
                $newRamStatus = $sessionManager->getRamStatus();
                if ($newRamStatus['is_critical'] && count($activeSessions) > 0) {
                    $this->warn('Still critical - force closing oldest session...');
                    $sessionManager->forceCloseOldestSession();
                }
            } else {
                $this->warn('Run with --auto-cleanup to automatically free RAM');
            }

            return Command::FAILURE;
        } elseif ($ramStatus['is_warning']) {
            $this->warn('⚠️  RAM usage is elevated');
            $this->line('Consider monitoring closely or closing unused sessions.');
            return Command::SUCCESS;
        }

        $this->info('✅ RAM usage is healthy');
        return Command::SUCCESS;
    }
}
