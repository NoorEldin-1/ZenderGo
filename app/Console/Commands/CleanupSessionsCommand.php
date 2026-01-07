<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SessionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cleanup orphaned WPPConnect sessions and sync state
 */
class CleanupSessionsCommand extends Command
{
    protected $signature = 'sessions:full-cleanup 
                            {--force : Force close all sessions without confirmation}
                            {--sync : Sync session state with wppconnect-server}
                            {--clear-failed : Clear failed jobs}';

    protected $description = 'Cleanup orphaned WPPConnect sessions and sync tracking state';

    protected SessionManager $sessionManager;
    protected string $baseUrl;
    protected string $secretKey;

    public function __construct()
    {
        parent::__construct();
        $this->sessionManager = new SessionManager();
    }

    public function handle(): int
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
        $this->secretKey = config('services.whatsapp.secret_key', 'THISISMYSECURETOKEN');

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║          WPPConnect Sessions Cleanup v1.0                  ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        // Step 1: Get current state
        $this->analyzeCurrentState();

        // Step 2: Sync if requested
        if ($this->option('sync')) {
            $this->syncSessionsWithServer();
        }

        // Step 3: Cleanup orphaned sessions
        $this->cleanupOrphanedSessions();

        // Step 4: Cleanup idle sessions
        $this->cleanupIdleSessions();

        // Step 5: Clear failed jobs if requested
        if ($this->option('clear-failed')) {
            $this->clearFailedJobs();
        }

        // Step 6: Clear cache tracking
        $this->clearCacheTracking();

        // Final state
        $this->info('');
        $this->info('✅ Cleanup completed!');
        $this->showFinalState();

        return Command::SUCCESS;
    }

    protected function analyzeCurrentState(): void
    {
        $this->info('📊 Analyzing current state...');
        $this->info('');

        // Get wppconnect-server sessions
        $serverSessions = $this->getServerSessions();

        // Get tracked sessions in Laravel
        $trackedSessions = $this->sessionManager->getActiveSessions();

        // Get DB sessions
        $dbSessions = User::whereNotNull('whatsapp_session')->pluck('whatsapp_session', 'id')->toArray();

        // Get RAM status
        $ramStatus = $this->sessionManager->getRamStatus();

        $this->table(
            ['Metric', 'Value'],
            [
                ['WPPConnect Server Sessions', count($serverSessions)],
                ['Laravel Tracked Sessions', count($trackedSessions)],
                ['Users with Sessions (DB)', count($dbSessions)],
                ['RAM Usage', "{$ramStatus['used_mb']} MB / {$ramStatus['total_mb']} MB ({$ramStatus['usage_percent']}%)"],
                ['RAM Status', $ramStatus['is_critical'] ? '🚨 CRITICAL' : ($ramStatus['is_warning'] ? '⚠️ WARNING' : '✓ OK')],
            ]
        );

        if (count($serverSessions) > 0) {
            $this->info('');
            $this->info('📋 Sessions in WPPConnect Server:');
            foreach ($serverSessions as $session) {
                $this->line("   • {$session}");
            }
        }
    }

    protected function getServerSessions(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/{$this->secretKey}/show-all-sessions");
            if ($response->successful()) {
                return $response->json()['response'] ?? [];
            }
        } catch (\Exception $e) {
            $this->error("Cannot connect to wppconnect-server: " . $e->getMessage());
        }
        return [];
    }

    protected function syncSessionsWithServer(): void
    {
        $this->info('');
        $this->info('🔄 Syncing sessions with wppconnect-server...');

        $serverSessions = $this->getServerSessions();
        $dbSessions = User::whereNotNull('whatsapp_session')
            ->pluck('id', 'whatsapp_session')
            ->toArray();

        $synced = 0;
        foreach ($serverSessions as $sessionName) {
            if (isset($dbSessions[$sessionName])) {
                $userId = $dbSessions[$sessionName];
                $this->sessionManager->markSessionActive($userId, $sessionName);
                $this->line("   ✓ Synced session: {$sessionName} (User #{$userId})");
                $synced++;
            } else {
                $this->warn("   ⚠ Orphaned session (no user): {$sessionName}");
            }
        }

        $this->info("   Synced {$synced} sessions.");
    }

    protected function cleanupOrphanedSessions(): void
    {
        $this->info('');
        $this->info('🧹 Cleaning up orphaned sessions...');

        $serverSessions = $this->getServerSessions();
        $dbSessions = User::whereNotNull('whatsapp_session')
            ->pluck('whatsapp_token', 'whatsapp_session')
            ->toArray();

        $orphaned = 0;
        foreach ($serverSessions as $sessionName) {
            if (!isset($dbSessions[$sessionName])) {
                $this->warn("   Found orphaned: {$sessionName}");

                if ($this->option('force') || $this->confirm("   Close this orphaned session?")) {
                    $this->closeServerSession($sessionName);
                    $orphaned++;
                }
            }
        }

        if ($orphaned === 0) {
            $this->info('   No orphaned sessions to clean.');
        } else {
            $this->info("   Closed {$orphaned} orphaned sessions.");
        }
    }

    protected function cleanupIdleSessions(): void
    {
        $this->info('');
        $this->info('💤 Cleaning up idle sessions...');

        $idleSessions = $this->sessionManager->getIdleSessions();
        $closed = 0;

        foreach ($idleSessions as $userId => $data) {
            $idleMinutes = round((now()->timestamp - $data['last_activity']) / 60);
            $this->warn("   Found idle: {$data['session_name']} (User #{$userId}, idle {$idleMinutes} min)");

            if ($this->option('force') || $this->confirm("   Close this idle session?")) {
                $user = User::find($userId);
                if ($user && $this->sessionManager->closeSession($user)) {
                    $closed++;
                }
            }
        }

        if ($closed === 0 && count($idleSessions) === 0) {
            $this->info('   No idle sessions to clean.');
        } else {
            $this->info("   Closed {$closed} idle sessions.");
        }
    }

    protected function clearFailedJobs(): void
    {
        $this->info('');
        $this->info('🗑️ Clearing failed jobs...');

        $failedCount = DB::table('failed_jobs')->count();

        if ($failedCount === 0) {
            $this->info('   No failed jobs to clear.');
            return;
        }

        $this->warn("   Found {$failedCount} failed jobs.");

        if ($this->option('force') || $this->confirm("   Clear all failed jobs?")) {
            DB::table('failed_jobs')->truncate();
            $this->info("   Cleared {$failedCount} failed jobs.");
            Log::info("Cleared {$failedCount} failed jobs via sessions:cleanup");
        }
    }

    protected function clearCacheTracking(): void
    {
        $this->info('');
        $this->info('🔄 Refreshing cache tracking...');

        // Clear the session list cache to force rebuild
        Cache::forget('wpp_session:list');

        // Re-sync with current state
        $serverSessions = $this->getServerSessions();
        $dbSessions = User::whereNotNull('whatsapp_session')
            ->where('session_state', 'active')
            ->pluck('id', 'whatsapp_session')
            ->toArray();

        $newList = [];
        foreach ($serverSessions as $sessionName) {
            if (isset($dbSessions[$sessionName])) {
                $userId = $dbSessions[$sessionName];
                $this->sessionManager->markSessionActive($userId, $sessionName);
                $newList[] = $userId;
            }
        }

        $this->info('   Cache tracking refreshed.');
    }

    protected function closeServerSession(string $sessionName): bool
    {
        try {
            // First try to get token for this session
            $user = User::where('whatsapp_session', $sessionName)->first();
            $token = $user?->whatsapp_token ?? '';

            $response = Http::withToken($token)
                ->timeout(10)
                ->post("{$this->baseUrl}/api/{$sessionName}/close-session");

            if ($response->successful()) {
                $this->info("   ✓ Closed session: {$sessionName}");
                return true;
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to close: " . $e->getMessage());
        }
        return false;
    }

    protected function showFinalState(): void
    {
        $this->info('');
        $this->info('📊 Final State:');

        $serverSessions = $this->getServerSessions();
        $trackedSessions = $this->sessionManager->getActiveSessions();
        $ramStatus = $this->sessionManager->getRamStatus();
        $failedJobs = DB::table('failed_jobs')->count();

        $this->table(
            ['Metric', 'Value'],
            [
                ['WPPConnect Server Sessions', count($serverSessions)],
                ['Laravel Tracked Sessions', count($trackedSessions)],
                ['Failed Jobs', $failedJobs],
                ['RAM Usage', "{$ramStatus['used_mb']} MB / {$ramStatus['total_mb']} MB ({$ramStatus['usage_percent']}%)"],
            ]
        );
    }
}
