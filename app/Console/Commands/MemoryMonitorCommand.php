<?php

namespace App\Console\Commands;

use App\Services\SessionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Real-time memory monitoring for WPPConnect sessions
 */
class MemoryMonitorCommand extends Command
{
    protected $signature = 'memory:monitor 
                            {--interval=5 : Interval between checks in seconds}
                            {--duration=60 : Total monitoring duration in seconds}
                            {--alert-threshold=80 : RAM usage percentage to trigger alert}';

    protected $description = 'Monitor RAM usage and WPPConnect sessions in real-time';

    protected SessionManager $sessionManager;

    public function __construct()
    {
        parent::__construct();
        $this->sessionManager = new SessionManager();
    }

    public function handle(): int
    {
        $interval = (int) $this->option('interval');
        $duration = (int) $this->option('duration');
        $alertThreshold = (int) $this->option('alert-threshold');

        $iterations = ceil($duration / $interval);

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║           WPPConnect Memory Monitor v1.0                   ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');
        $this->info("⏱️  Monitoring for {$duration}s with {$interval}s interval");
        $this->info("🚨 Alert threshold: {$alertThreshold}%");
        $this->info('');
        $this->info('Press Ctrl+C to stop');
        $this->info('');

        $history = [];

        for ($i = 0; $i < $iterations; $i++) {
            $snapshot = $this->takeSnapshot();
            $history[] = $snapshot;

            $this->renderSnapshot($snapshot, $alertThreshold);

            if ($i < $iterations - 1) {
                sleep($interval);
            }
        }

        $this->generateSummary($history);

        return Command::SUCCESS;
    }

    protected function takeSnapshot(): array
    {
        $ramStatus = $this->sessionManager->getRamStatus();
        $activeSessions = $this->sessionManager->getActiveSessionCount();
        $idleSessions = count($this->sessionManager->getIdleSessions());

        // Get Node.js memory
        $nodeMemory = 0;
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('powershell "Get-Process -Name node -ErrorAction SilentlyContinue | Select-Object -ExpandProperty WorkingSet64"');
            if (trim($output)) {
                $nodeMemory = round((int) trim($output) / 1024 / 1024, 1);
            }
        }

        // Get wppconnect sessions from server
        $wppSessions = 0;
        try {
            $baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
            $secretKey = config('services.whatsapp.secret_key', 'THISISMYSECURETOKEN');
            $response = Http::timeout(5)->get("{$baseUrl}/api/{$secretKey}/show-all-sessions");
            if ($response->successful()) {
                $data = $response->json();
                $wppSessions = count($data['response'] ?? []);
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return [
            'timestamp' => now()->format('H:i:s'),
            'system_ram_used' => $ramStatus['used_mb'],
            'system_ram_total' => $ramStatus['total_mb'],
            'system_ram_percent' => $ramStatus['usage_percent'],
            'php_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 1),
            'node_memory_mb' => $nodeMemory,
            'active_sessions' => $activeSessions,
            'idle_sessions' => $idleSessions,
            'wpp_server_sessions' => $wppSessions,
            'is_warning' => $ramStatus['is_warning'],
            'is_critical' => $ramStatus['is_critical'],
        ];
    }

    protected function renderSnapshot(array $snapshot, int $alertThreshold): void
    {
        $ramBar = $this->createProgressBar($snapshot['system_ram_percent'], 30);
        $status = $snapshot['system_ram_percent'] >= $alertThreshold ? '🚨' : '✓';

        $line = sprintf(
            "[%s] RAM: %s %d%% | Node: %dMB | Sessions: %d active, %d idle, %d wpp | %s",
            $snapshot['timestamp'],
            $ramBar,
            $snapshot['system_ram_percent'],
            $snapshot['node_memory_mb'],
            $snapshot['active_sessions'],
            $snapshot['idle_sessions'],
            $snapshot['wpp_server_sessions'],
            $status
        );

        if ($snapshot['is_critical']) {
            $this->error($line);
        } elseif ($snapshot['is_warning']) {
            $this->warn($line);
        } else {
            $this->info($line);
        }
    }

    protected function createProgressBar(float $percentage, int $width): string
    {
        $filled = (int) round($percentage / 100 * $width);
        $empty = $width - $filled;

        return '[' . str_repeat('█', $filled) . str_repeat('░', $empty) . ']';
    }

    protected function generateSummary(array $history): void
    {
        if (empty($history)) {
            return;
        }

        $ramValues = array_column($history, 'system_ram_percent');
        $nodeValues = array_column($history, 'node_memory_mb');
        $sessionValues = array_column($history, 'active_sessions');

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  MONITORING SUMMARY');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        $this->table(
            ['Metric', 'Min', 'Max', 'Average'],
            [
                [
                    'System RAM %',
                    min($ramValues) . '%',
                    max($ramValues) . '%',
                    round(array_sum($ramValues) / count($ramValues), 1) . '%'
                ],
                [
                    'Node.js Memory (MB)',
                    min($nodeValues),
                    max($nodeValues),
                    round(array_sum($nodeValues) / count($nodeValues), 1)
                ],
                [
                    'Active Sessions',
                    min($sessionValues),
                    max($sessionValues),
                    round(array_sum($sessionValues) / count($sessionValues), 1)
                ],
            ]
        );

        // Trend analysis
        $firstRam = $history[0]['system_ram_percent'];
        $lastRam = end($history)['system_ram_percent'];
        $ramTrend = $lastRam - $firstRam;

        $this->info('');
        if ($ramTrend > 5) {
            $this->warn("⚠️  RAM trend: +{$ramTrend}% (INCREASING - potential leak)");
        } elseif ($ramTrend < -5) {
            $this->info("✅ RAM trend: {$ramTrend}% (DECREASING - cleanup working)");
        } else {
            $this->info("✓  RAM trend: {$ramTrend}% (STABLE)");
        }
    }
}
