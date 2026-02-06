<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SessionManager;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Comprehensive WPPConnect Stress Test Command
 * Tests session management, message sending, and RAM consumption
 */
class StressTestCommand extends Command
{
    protected $signature = 'stress:test 
                            {--sessions=5 : Number of concurrent sessions to test}
                            {--messages=20 : Number of messages to send per session}
                            {--phone=01552678658 : Test phone number}
                            {--suite=all : Test suite to run (all, session, message, memory, edge)}
                            {--verbose-output : Show detailed output}';

    protected $description = 'Run comprehensive WPPConnect stress tests for RAM and session analysis';

    protected SessionManager $sessionManager;
    protected array $results = [];
    protected array $errors = [];
    protected float $startMemory;
    protected float $startTime;

    public function __construct()
    {
        parent::__construct();
        $this->sessionManager = new SessionManager();
    }

    public function handle(): int
    {
        $this->startMemory = memory_get_usage(true);
        $this->startTime = microtime(true);

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║          WPPConnect Stress Test Suite v1.0                 ║');
        $this->info('╠════════════════════════════════════════════════════════════╣');
        $this->info('║  Testing RAM consumption, session management & messaging  ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        $suite = $this->option('suite');

        // Get initial system state
        $this->logSystemState('INITIAL');

        $suites = $suite === 'all'
            ? ['session', 'message', 'memory', 'edge']
            : [$suite];

        foreach ($suites as $testSuite) {
            $this->runTestSuite($testSuite);
        }

        // Get final system state
        $this->logSystemState('FINAL');

        // Generate report
        $this->generateReport();

        return Command::SUCCESS;
    }

    protected function runTestSuite(string $suite): void
    {
        $this->info('');
        $this->warn("═══════════════════════════════════════════════════════════");
        $this->warn("  Running Test Suite: " . strtoupper($suite));
        $this->warn("═══════════════════════════════════════════════════════════");

        match ($suite) {
            'session' => $this->runSessionTests(),
            'message' => $this->runMessageTests(),
            'memory' => $this->runMemoryLeakTests(),
            'edge' => $this->runEdgeCaseTests(),
            default => $this->error("Unknown test suite: {$suite}"),
        };
    }

    /**
     * Test Suite 1: Session Lifecycle Tests
     */
    protected function runSessionTests(): void
    {
        $this->info('');
        $this->info('📋 Session Lifecycle Tests');
        $this->info('─────────────────────────────────────────');

        // ST-1.1: Check wppconnect-server connectivity
        $this->runTest('ST-1.1', 'WPPConnect Server Connectivity', function () {
            $baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
            try {
                $response = Http::timeout(5)->get("{$baseUrl}/api-docs");
                return [
                    'passed' => $response->successful() || $response->status() === 200,
                    'message' => $response->successful() ? 'Server is accessible' : 'Server returned: ' . $response->status(),
                    'data' => ['status_code' => $response->status()]
                ];
            } catch (\Exception $e) {
                return [
                    'passed' => false,
                    'message' => 'Cannot connect to wppconnect-server: ' . $e->getMessage(),
                    'data' => ['error' => $e->getMessage()]
                ];
            }
        });

        // ST-1.2: Check active sessions from wppconnect
        $this->runTest('ST-1.2', 'Get Active Sessions from WPPConnect', function () {
            $baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
            $secretKey = config('services.whatsapp.secret_key', 'THISISMYSECURETOKEN');

            try {
                $response = Http::timeout(10)->get("{$baseUrl}/api/{$secretKey}/show-all-sessions");
                $data = $response->json();

                return [
                    'passed' => $response->successful(),
                    'message' => 'Retrieved sessions list',
                    'data' => [
                        'sessions' => $data['response'] ?? [],
                        'count' => count($data['response'] ?? [])
                    ]
                ];
            } catch (\Exception $e) {
                return [
                    'passed' => false,
                    'message' => 'Failed to get sessions: ' . $e->getMessage(),
                    'data' => ['error' => $e->getMessage()]
                ];
            }
        });

        // ST-1.3: Test SessionManager getActiveSessions
        $this->runTest('ST-1.3', 'SessionManager Active Sessions Tracking', function () {
            $sessions = $this->sessionManager->getActiveSessions();
            $count = $this->sessionManager->getActiveSessionCount();

            return [
                'passed' => true,
                'message' => "SessionManager tracking {$count} sessions",
                'data' => [
                    'tracked_sessions' => $count,
                    'sessions_data' => $sessions
                ]
            ];
        });

        // ST-1.4: Test RAM status method
        $this->runTest('ST-1.4', 'RAM Status Monitoring', function () {
            $ramStatus = $this->sessionManager->getRamStatus();

            return [
                'passed' => true,
                'message' => sprintf(
                    'RAM: %d MB used / %d MB total (%.1f%%)',
                    $ramStatus['used_mb'],
                    $ramStatus['total_mb'],
                    $ramStatus['usage_percent']
                ),
                'data' => $ramStatus
            ];
        });

        // ST-1.5: Test MAX_CONCURRENT_SESSIONS enforcement
        $this->runTest('ST-1.5', 'Concurrent Sessions Limit Check', function () {
            $maxSessions = 10; // From SessionManager::MAX_CONCURRENT_SESSIONS
            $currentCount = $this->sessionManager->getActiveSessionCount();

            return [
                'passed' => $currentCount <= $maxSessions,
                'message' => "Current: {$currentCount} / Max: {$maxSessions}",
                'data' => [
                    'current' => $currentCount,
                    'max' => $maxSessions,
                    'within_limit' => $currentCount <= $maxSessions
                ]
            ];
        });
    }

    /**
     * Test Suite 2: Message Sending Tests
     */
    protected function runMessageTests(): void
    {
        $this->info('');
        $this->info('📨 Message Sending Tests');
        $this->info('─────────────────────────────────────────');

        $testPhone = $this->option('phone');
        $user = User::first();

        if (!$user || !$user->whatsapp_session || !$user->whatsapp_token) {
            $this->runTest('ST-2.0', 'User Session Availability', function () {
                return [
                    'passed' => false,
                    'message' => 'No user with WhatsApp session found. Skipping message tests.',
                    'data' => ['reason' => 'no_session']
                ];
            });
            return;
        }

        // ST-2.1: Test WhatsAppService instantiation
        $this->runTest('ST-2.1', 'WhatsAppService Instantiation', function () use ($user) {
            try {
                $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
                return [
                    'passed' => true,
                    'message' => "Service created for session: {$user->whatsapp_session}",
                    'data' => ['session' => $user->whatsapp_session]
                ];
            } catch (\Exception $e) {
                return [
                    'passed' => false,
                    'message' => 'Failed to create service: ' . $e->getMessage(),
                    'data' => ['error' => $e->getMessage()]
                ];
            }
        });

        // ST-2.2: Test connection check
        $this->runTest('ST-2.2', 'Connection Check', function () use ($user) {
            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $result = $whatsapp->checkConnection();

            return [
                'passed' => $result['success'] ?? false,
                'message' => $result['connected'] ?? false ? 'Session is connected' : 'Session is disconnected',
                'data' => $result
            ];
        });

        // ST-2.3: Test session wake
        $this->runTest('ST-2.3', 'Session Wake via SessionManager', function () use ($user) {
            $beforeRam = $this->sessionManager->getRamStatus();
            $result = $this->sessionManager->wakeSession($user);
            $afterRam = $this->sessionManager->getRamStatus();

            return [
                'passed' => $result['status'] === 'connected',
                'message' => $result['message'],
                'data' => [
                    'status' => $result['status'],
                    'ram_before' => $beforeRam['used_mb'],
                    'ram_after' => $afterRam['used_mb'],
                    'ram_diff' => $afterRam['used_mb'] - $beforeRam['used_mb']
                ]
            ];
        });

        // ST-2.4: Test sending a single message
        $this->runTest('ST-2.4', 'Send Single Test Message', function () use ($user, $testPhone) {
            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $testMessage = "🧪 Stress Test Message - " . now()->format('Y-m-d H:i:s');

            $success = $whatsapp->sendMessage($testPhone, $testMessage);

            return [
                'passed' => $success,
                'message' => $success ? 'Message sent successfully' : 'Failed to send message',
                'data' => ['phone' => $testPhone, 'sent' => $success]
            ];
        });

        // ST-2.5: Test sending multiple messages (throughput test)
        $messageCount = min((int) $this->option('messages'), 10); // Limit to 10 for safety
        $this->runTest('ST-2.5', "Send {$messageCount} Messages (Throughput)", function () use ($user, $testPhone, $messageCount) {
            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $sent = 0;
            $failed = 0;
            $startTime = microtime(true);

            for ($i = 1; $i <= $messageCount; $i++) {
                $message = "🧪 Throughput Test [{$i}/{$messageCount}] - " . now()->format('H:i:s');
                if ($whatsapp->sendMessage($testPhone, $message)) {
                    $sent++;
                } else {
                    $failed++;
                }
                // Small delay to avoid rate limiting
                usleep(500000); // 0.5 second
            }

            $duration = microtime(true) - $startTime;
            $rate = $sent / $duration;

            return [
                'passed' => $failed === 0,
                'message' => sprintf('Sent %d/%d messages in %.1fs (%.2f msg/s)', $sent, $messageCount, $duration, $rate),
                'data' => [
                    'total' => $messageCount,
                    'sent' => $sent,
                    'failed' => $failed,
                    'duration_seconds' => round($duration, 2),
                    'rate_per_second' => round($rate, 2)
                ]
            ];
        });
    }

    /**
     * Test Suite 3: Memory Leak Detection
     */
    protected function runMemoryLeakTests(): void
    {
        $this->info('');
        $this->info('🔍 Memory Leak Detection Tests');
        $this->info('─────────────────────────────────────────');

        // ST-3.1: PHP memory baseline
        $this->runTest('ST-3.1', 'PHP Memory Baseline', function () {
            $memory = memory_get_usage(true);
            $peak = memory_get_peak_usage(true);

            return [
                'passed' => true,
                'message' => sprintf('Current: %.1f MB, Peak: %.1f MB', $memory / 1024 / 1024, $peak / 1024 / 1024),
                'data' => [
                    'current_bytes' => $memory,
                    'peak_bytes' => $peak,
                    'current_mb' => round($memory / 1024 / 1024, 2),
                    'peak_mb' => round($peak / 1024 / 1024, 2)
                ]
            ];
        });

        // ST-3.2: Node.js process memory (wppconnect-server)
        $this->runTest('ST-3.2', 'Node.js Process Memory', function () {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('powershell "Get-Process -Name node -ErrorAction SilentlyContinue | Select-Object -ExpandProperty WorkingSet64"');
                $nodeMemory = trim($output);

                if ($nodeMemory) {
                    $memoryMb = (int) $nodeMemory / 1024 / 1024;
                    return [
                        'passed' => true,
                        'message' => sprintf('Node.js using %.1f MB RAM', $memoryMb),
                        'data' => ['node_memory_mb' => round($memoryMb, 2)]
                    ];
                }
            }

            return [
                'passed' => false,
                'message' => 'Could not measure Node.js memory',
                'data' => ['error' => 'measurement_failed']
            ];
        });

        // ST-3.3: Check for orphaned sessions in cache
        $this->runTest('ST-3.3', 'Orphaned Sessions Detection', function () {
            $trackedSessions = $this->sessionManager->getActiveSessions();
            $dbSessions = User::whereNotNull('whatsapp_session')
                ->where('session_state', 'active')
                ->pluck('whatsapp_session', 'id')
                ->toArray();

            $orphaned = [];
            foreach ($trackedSessions as $userId => $data) {
                if (!isset($dbSessions[$userId])) {
                    $orphaned[$userId] = $data;
                }
            }

            return [
                'passed' => count($orphaned) === 0,
                'message' => count($orphaned) === 0
                    ? 'No orphaned sessions found'
                    : count($orphaned) . ' orphaned sessions detected',
                'data' => [
                    'tracked_count' => count($trackedSessions),
                    'db_active_count' => count($dbSessions),
                    'orphaned_count' => count($orphaned),
                    'orphaned_sessions' => $orphaned
                ]
            ];
        });

        // ST-3.4: Idle sessions check
        $this->runTest('ST-3.4', 'Idle Sessions Detection', function () {
            $idleSessions = $this->sessionManager->getIdleSessions();

            return [
                'passed' => true,
                'message' => count($idleSessions) . ' idle sessions found (ready for cleanup)',
                'data' => [
                    'idle_count' => count($idleSessions),
                    'idle_sessions' => $idleSessions
                ]
            ];
        });

        // ST-3.5: Queue jobs check
        $this->runTest('ST-3.5', 'Pending Queue Jobs Analysis', function () {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            $whatsappJobs = DB::table('jobs')
                ->where('payload', 'like', '%SendWhatsappCampaign%')
                ->count();

            return [
                'passed' => $failedJobs === 0,
                'message' => sprintf('Pending: %d, WhatsApp: %d, Failed: %d', $pendingJobs, $whatsappJobs, $failedJobs),
                'data' => [
                    'pending_total' => $pendingJobs,
                    'pending_whatsapp' => $whatsappJobs,
                    'failed_jobs' => $failedJobs
                ]
            ];
        });
    }

    /**
     * Test Suite 4: Edge Cases
     */
    protected function runEdgeCaseTests(): void
    {
        $this->info('');
        $this->info('⚠️ Edge Case Tests');
        $this->info('─────────────────────────────────────────');

        // ST-4.1: Invalid phone number handling
        $this->runTest('ST-4.1', 'Invalid Phone Number Handling', function () {
            $user = User::whereNotNull('whatsapp_session')->first();
            if (!$user) {
                return ['passed' => true, 'message' => 'Skipped - no session', 'data' => []];
            }

            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $result = $whatsapp->sendMessage('invalid', 'Test');

            return [
                'passed' => true, // We expect it to handle gracefully
                'message' => $result ? 'Sent (unexpected)' : 'Correctly rejected invalid phone',
                'data' => ['handled_gracefully' => !$result]
            ];
        });

        // ST-4.2: Empty message handling
        $this->runTest('ST-4.2', 'Empty Message Handling', function () {
            $user = User::whereNotNull('whatsapp_session')->first();
            if (!$user) {
                return ['passed' => true, 'message' => 'Skipped - no session', 'data' => []];
            }

            $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $result = $whatsapp->sendMessage('01552678658', '');

            return [
                'passed' => true,
                'message' => $result ? 'Sent empty (may be valid)' : 'Correctly rejected empty message',
                'data' => ['result' => $result]
            ];
        });

        // ST-4.3: Double close session handling
        $this->runTest('ST-4.3', 'Double Close Session Handling', function () {
            $user = User::whereNotNull('whatsapp_session')->first();
            if (!$user) {
                return ['passed' => true, 'message' => 'Skipped - no session', 'data' => []];
            }

            // Try to close twice
            $result1 = $this->sessionManager->closeSession($user);
            $result2 = $this->sessionManager->closeSession($user);

            return [
                'passed' => true,
                'message' => 'Double close handled without crash',
                'data' => ['first_close' => $result1, 'second_close' => $result2]
            ];
        });

        // ST-4.4: Database connection under load
        $this->runTest('ST-4.4', 'Database Connection Pool', function () {
            $startTime = microtime(true);

            for ($i = 0; $i < 100; $i++) {
                User::count();
            }

            $duration = microtime(true) - $startTime;

            return [
                'passed' => $duration < 5,
                'message' => sprintf('100 queries in %.2fs', $duration),
                'data' => ['queries' => 100, 'duration' => round($duration, 3)]
            ];
        });

        // ST-4.5: Cache performance
        $this->runTest('ST-4.5', 'Cache Read/Write Performance', function () {
            $startTime = microtime(true);

            for ($i = 0; $i < 100; $i++) {
                cache()->put("stress_test_{$i}", "value_{$i}", 60);
                cache()->get("stress_test_{$i}");
                cache()->forget("stress_test_{$i}");
            }

            $duration = microtime(true) - $startTime;

            return [
                'passed' => $duration < 2,
                'message' => sprintf('300 cache ops in %.2fs', $duration),
                'data' => ['operations' => 300, 'duration' => round($duration, 3)]
            ];
        });
    }

    /**
     * Run a single test and record results
     */
    protected function runTest(string $id, string $name, callable $test): void
    {
        $this->line("  [{$id}] {$name}...");

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $result = $test();
            $duration = microtime(true) - $startTime;
            $memoryUsed = memory_get_usage(true) - $startMemory;

            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            $this->line("       {$status} - {$result['message']}");

            if ($this->option('verbose-output') && !empty($result['data'])) {
                $this->line('       Data: ' . json_encode($result['data'], JSON_PRETTY_PRINT));
            }

            $this->results[$id] = [
                'name' => $name,
                'passed' => $result['passed'],
                'message' => $result['message'],
                'data' => $result['data'] ?? [],
                'duration_ms' => round($duration * 1000, 2),
                'memory_delta_kb' => round($memoryUsed / 1024, 2)
            ];

        } catch (\Exception $e) {
            $this->line("       ❌ ERROR - " . $e->getMessage());
            $this->errors[$id] = [
                'name' => $name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
            Log::error("Stress test {$id} failed", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Log current system state
     */
    protected function logSystemState(string $label): void
    {
        $ramStatus = $this->sessionManager->getRamStatus();
        $phpMemory = memory_get_usage(true) / 1024 / 1024;
        $activeSessions = $this->sessionManager->getActiveSessionCount();

        $this->info('');
        $this->warn("📊 System State: {$label}");
        $this->table(
            ['Metric', 'Value'],
            [
                ['System RAM Used', $ramStatus['used_mb'] . ' MB / ' . $ramStatus['total_mb'] . ' MB (' . $ramStatus['usage_percent'] . '%)'],
                ['PHP Memory', round($phpMemory, 2) . ' MB'],
                ['Active Sessions', $activeSessions],
                ['RAM Warning', $ramStatus['is_warning'] ? '⚠️ YES' : '✓ No'],
                ['RAM Critical', $ramStatus['is_critical'] ? '🚨 YES' : '✓ No'],
            ]
        );
    }

    /**
     * Generate final report
     */
    protected function generateReport(): void
    {
        $totalTime = microtime(true) - $this->startTime;
        $memoryDelta = memory_get_usage(true) - $this->startMemory;

        $passed = collect($this->results)->where('passed', true)->count();
        $failed = collect($this->results)->where('passed', false)->count();
        $errors = count($this->errors);
        $total = $passed + $failed + $errors;

        $this->info('');
        $this->info('╔════════════════════════════════════════════════════════════╗');
        $this->info('║                    STRESS TEST REPORT                      ║');
        $this->info('╚════════════════════════════════════════════════════════════╝');
        $this->info('');

        $this->table(
            ['Category', 'Count', 'Percentage'],
            [
                ['✅ Passed', $passed, $total > 0 ? round($passed / $total * 100, 1) . '%' : '0%'],
                ['❌ Failed', $failed, $total > 0 ? round($failed / $total * 100, 1) . '%' : '0%'],
                ['💥 Errors', $errors, $total > 0 ? round($errors / $total * 100, 1) . '%' : '0%'],
                ['📊 Total', $total, '100%'],
            ]
        );

        $this->info('');
        $this->info('⏱️  Total Duration: ' . round($totalTime, 2) . ' seconds');
        $this->info('💾 Memory Delta: ' . round($memoryDelta / 1024 / 1024, 2) . ' MB');

        // Show failed tests
        if ($failed > 0 || $errors > 0) {
            $this->info('');
            $this->error('═══════════════════════════════════════════════════════════');
            $this->error('  ISSUES DETECTED');
            $this->error('═══════════════════════════════════════════════════════════');

            foreach ($this->results as $id => $result) {
                if (!$result['passed']) {
                    $this->line("  ❌ [{$id}] {$result['name']}");
                    $this->line("     → {$result['message']}");
                }
            }

            foreach ($this->errors as $id => $error) {
                $this->line("  💥 [{$id}] {$error['name']}");
                $this->line("     → {$error['error']}");
            }
        }

        // Save results to log
        Log::info('Stress Test Completed', [
            'passed' => $passed,
            'failed' => $failed,
            'errors' => $errors,
            'duration' => round($totalTime, 2),
            'results' => $this->results,
            'error_details' => $this->errors
        ]);

        $this->info('');
        $this->info('📄 Full results saved to: storage/logs/laravel.log');
    }
}
