<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Health check controller for monitoring server status.
 * 
 * This endpoint is designed for:
 * - Load balancer health checks
 * - Monitoring systems (Uptime Robot, Pingdom, etc.)
 * - Quick diagnostics during deployment
 * 
 * Access: GET /health
 */
class HealthController extends Controller
{
    /**
     * Check overall system health.
     * 
     * Returns 200 if all systems operational, 503 if any critical system is down.
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $allHealthy = !in_array(false, array_column($checks, 'healthy'), true);
        $criticalDown = !$checks['database']['healthy'];

        return response()->json([
            'status' => $allHealthy ? 'healthy' : ($criticalDown ? 'critical' : 'degraded'),
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            ],
        ], $criticalDown ? 503 : ($allHealthy ? 200 : 200));
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy' => true,
                'latency_ms' => $latency,
                'driver' => config('database.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Database connection failed', ['error' => $e->getMessage()]);
            return [
                'healthy' => false,
                'error' => 'Connection failed',
            ];
        }
    }

    /**
     * Check cache system.
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . uniqid();
            $start = microtime(true);

            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy' => $value === 'test',
                'latency_ms' => $latency,
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Cache system failed', ['error' => $e->getMessage()]);
            return [
                'healthy' => false,
                'error' => 'Cache operation failed',
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Check queue system.
     */
    private function checkQueue(): array
    {
        $driver = config('queue.default');

        try {
            if ($driver === 'redis') {
                return $this->checkRedisQueue();
            } elseif ($driver === 'database') {
                return $this->checkDatabaseQueue();
            }

            return [
                'healthy' => true,
                'driver' => $driver,
                'message' => 'Queue driver does not support health checks',
            ];
        } catch (\Exception $e) {
            Log::error('Health check: Queue system failed', ['error' => $e->getMessage()]);
            return [
                'healthy' => false,
                'error' => 'Queue check failed',
                'driver' => $driver,
            ];
        }
    }

    /**
     * Check Redis queue.
     */
    private function checkRedisQueue(): array
    {
        $start = microtime(true);
        Redis::ping();
        $latency = round((microtime(true) - $start) * 1000, 2);

        // Check pending jobs
        $pendingJobs = Redis::llen('queues:default') ?? 0;

        return [
            'healthy' => true,
            'latency_ms' => $latency,
            'driver' => 'redis',
            'pending_jobs' => $pendingJobs,
            'warning' => $pendingJobs > 5000 ? 'High queue backlog' : null,
        ];
    }

    /**
     * Check database queue.
     */
    private function checkDatabaseQueue(): array
    {
        $start = microtime(true);
        $pendingJobs = DB::table('jobs')->count();
        $latency = round((microtime(true) - $start) * 1000, 2);

        return [
            'healthy' => true,
            'latency_ms' => $latency,
            'driver' => 'database',
            'pending_jobs' => $pendingJobs,
            'warning' => $pendingJobs > 1000 ? 'High queue backlog - consider switching to Redis' : null,
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
