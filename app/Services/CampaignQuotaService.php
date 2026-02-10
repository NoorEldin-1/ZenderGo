<?php

namespace App\Services;

use App\Models\CampaignQuota;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * CampaignQuotaService - Manages user message quotas with Redis atomic operations.
 * 
 * Architecture:
 * - Redis is used for atomic increment/decrement operations to prevent race conditions
 * - Database is the source of truth for persistence
 * - Graceful failover to database-only if Redis is unavailable
 */
class CampaignQuotaService
{
    /**
     * Redis key prefix for quota counters.
     */
    protected const REDIS_PREFIX = 'quota:';

    /**
     * Cache TTL in seconds (5 hours default).
     */
    protected int $cacheTtl;

    /**
     * Whether Redis is available for atomic operations.
     */
    protected bool $redisAvailable;

    public function __construct()
    {
        $this->cacheTtl = SystemSetting::getCampaignQuotaWindowHours() * 3600;
        $this->redisAvailable = $this->checkRedisConnection();
    }

    /**
     * Check if Redis connection is available.
     * Works with both phpredis extension and predis pure PHP client.
     */
    protected function checkRedisConnection(): bool
    {
        try {
            // Test connection - works with both phpredis and predis
            $client = config('database.redis.client', 'phpredis');

            if ($client === 'predis') {
                // Predis: ping returns string "PONG"
                $result = Redis::connection()->client()->ping();
                return $result === 'PONG' || $result === true;
            } else {
                // phpredis: check extension first
                if (!extension_loaded('redis')) {
                    Log::info("Redis extension not installed, using database fallback for quota operations.");
                    return false;
                }
                Redis::ping();
                return true;
            }
        } catch (\Exception $e) {
            Log::warning("Redis unavailable, falling back to database for quota operations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Redis key for user quota counter.
     */
    protected function getRedisKey(int $userId, string $suffix = 'count'): string
    {
        return self::REDIS_PREFIX . "{$userId}:{$suffix}";
    }

    /**
     * Get cache key for user quota (for object caching).
     */
    protected function getCacheKey(int $userId): string
    {
        return "campaign_quota:{$userId}";
    }

    /**
     * Get or create quota record for user.
     */
    public function getQuota(User $user): CampaignQuota
    {
        // Try to get from cache first (for non-count data)
        $cacheKey = $this->getCacheKey($user->id);
        $quota = Cache::get($cacheKey);

        if ($quota instanceof CampaignQuota) {
            // Refresh from DB if window might be expired
            if ($quota->isWindowExpired()) {
                Cache::forget($cacheKey);
                $this->resetRedisCounter($user->id);
                $quota = null;
            }
        } else {
            $quota = null;
        }

        if (!$quota) {
            $quota = CampaignQuota::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'contacts_sent' => 0,
                    'window_starts_at' => now(),
                    'window_ends_at' => now()->addHours(SystemSetting::getCampaignQuotaWindowHours()),
                ]
            );

            // If window is expired, reset it
            if ($quota->isWindowExpired()) {
                $quota->resetWindow();
                $quota->refresh();
                $this->resetRedisCounter($user->id);
            }

            // Sync Redis counter with DB value
            if ($this->redisAvailable) {
                $this->syncRedisWithDb($user->id, $quota->contacts_sent);
            }

            // Cache the quota object
            Cache::put($cacheKey, $quota, $this->cacheTtl);
        }

        // If Redis is available, get the real-time count from Redis
        if ($this->redisAvailable) {
            $redisCount = $this->getRedisCounter($user->id);
            if ($redisCount !== null && $redisCount !== $quota->contacts_sent) {
                $quota->contacts_sent = $redisCount;
            }
        }

        return $quota;
    }

    /**
     * Check if user can send to N contacts using ATOMIC Redis operations.
     * This is the primary method to prevent race conditions.
     */
    public function canSend(User $user, int $contactCount): bool
    {
        // Limits removed - unlimited sending
        return true;
    }

    /**
     * Atomic check using Redis - prevents race conditions.
     */
    protected function atomicCanSend(int $userId, int $contactCount, int $limit): bool
    {
        $key = $this->getRedisKey($userId);

        try {
            // Get current value atomically
            $current = (int) Redis::get($key);

            Log::debug("Redis atomic check", [
                'user_id' => $userId,
                'current' => $current,
                'requested' => $contactCount,
                'limit' => $limit,
            ]);

            // Check if adding contactCount would exceed limit
            return ($current + $contactCount) <= $limit;
        } catch (\Exception $e) {
            Log::error("Redis atomicCanSend failed: " . $e->getMessage());
            // Fallback to simple check
            $quota = CampaignQuota::where('user_id', $userId)->first();
            return $quota ? $quota->canSendTo($contactCount) : true;
        }
    }

    /**
     * Database-based check with self-healing (fallback).
     */
    protected function databaseCanSend(User $user, int $contactCount, int $limit): bool
    {
        // Force a fresh read from the database
        $dbQuota = CampaignQuota::where('user_id', $user->id)->first();

        if (!$dbQuota) {
            return true;
        }

        if ($dbQuota->isWindowExpired()) {
            $dbQuota->resetWindow();
            return true;
        }

        $currentUsed = $dbQuota->contacts_sent;
        $canSend = ($currentUsed + $contactCount) <= $limit;

        Log::info("Database quota check for user {$user->id}", [
            'current' => $currentUsed,
            'requested' => $contactCount,
            'limit' => $limit,
            'can_send' => $canSend,
        ]);

        return $canSend;
    }

    /**
     * Record usage ATOMICALLY after campaign dispatch.
     * Uses Redis INCRBY for atomic increment, then syncs to DB.
     */
    public function recordUsage(User $user, int $contactCount): void
    {
        if ($this->redisAvailable) {
            $this->atomicRecordUsage($user, $contactCount);
        } else {
            $this->databaseRecordUsage($user, $contactCount);
        }
    }

    /**
     * Atomic usage recording with Redis INCRBY.
     */
    protected function atomicRecordUsage(User $user, int $contactCount): void
    {
        $key = $this->getRedisKey($user->id);

        try {
            // Atomic increment in Redis
            $newTotal = Redis::incrby($key, $contactCount);

            // Set expiry on key to match quota window
            Redis::expire($key, $this->cacheTtl);

            Log::info("Redis atomic increment for user {$user->id}", [
                'added' => $contactCount,
                'new_total' => $newTotal,
            ]);

            // Sync to database (async would be even better, but sync is safer)
            $quota = CampaignQuota::where('user_id', $user->id)->first();
            if ($quota) {
                if ($quota->isWindowExpired()) {
                    $quota->resetWindow();
                }
                $quota->contacts_sent = $newTotal;
                $quota->save();

                // Update cache
                Cache::put($this->getCacheKey($user->id), $quota, $this->cacheTtl);
            }
        } catch (\Exception $e) {
            Log::error("Redis atomicRecordUsage failed, falling back to DB: " . $e->getMessage());
            $this->databaseRecordUsage($user, $contactCount);
        }
    }

    /**
     * Database-based usage recording (fallback).
     */
    protected function databaseRecordUsage(User $user, int $contactCount): void
    {
        $quota = $this->getQuota($user);

        if ($quota->isWindowExpired()) {
            $quota->resetWindow();
        }

        $quota->recordUsage($contactCount);
        $quota->refresh();

        Cache::put($this->getCacheKey($user->id), $quota, $this->cacheTtl);

        Log::info("Campaign quota updated (DB) for user {$user->id}: sent {$contactCount}, total {$quota->contacts_sent}");
    }

    /**
     * Atomic reservation - reserves quota BEFORE dispatching jobs.
     * Returns true if reservation successful, false if would exceed limit.
     * 
     * This is the KEY method for preventing race conditions:
     * 1. Check if adding count would exceed limit
     * 2. If OK, atomically increment counter
     * 3. If not OK, return false
     */
    public function reserveQuota(User $user, int $contactCount): bool
    {
        // Limits removed - always allow reservation
        return true;
    }

    /**
     * Atomic reservation using Redis Lua script.
     */
    protected function atomicReserve(int $userId, int $contactCount, int $limit): bool
    {
        $key = $this->getRedisKey($userId);

        try {
            // Lua script for atomic check-and-increment
            $script = <<<'LUA'
                local current = tonumber(redis.call('GET', KEYS[1]) or 0)
                local increment = tonumber(ARGV[1])
                local limit = tonumber(ARGV[2])
                
                if (current + increment) <= limit then
                    redis.call('INCRBY', KEYS[1], increment)
                    return current + increment
                else
                    return -1
                end
            LUA;

            $result = Redis::eval($script, 1, $key, $contactCount, $limit);

            if ($result == -1) {
                Log::info("Quota reservation DENIED for user {$userId} (atomic)", [
                    'requested' => $contactCount,
                    'limit' => $limit,
                ]);
                return false;
            }

            // Set expiry
            Redis::expire($key, $this->cacheTtl);

            Log::info("Quota reservation SUCCESS for user {$userId} (atomic)", [
                'reserved' => $contactCount,
                'new_total' => $result,
            ]);

            // Sync to DB
            $this->syncDbFromRedis($userId, $result);

            return true;
        } catch (\Exception $e) {
            Log::error("Redis atomicReserve failed: " . $e->getMessage());
            // Fall back to database
            $user = User::find($userId);
            return $user ? $this->databaseReserve($user, $contactCount, $limit) : false;
        }
    }

    /**
     * Database-based reservation with row locking.
     */
    protected function databaseReserve(User $user, int $contactCount, int $limit): bool
    {
        // Use pessimistic locking to prevent race conditions
        return \DB::transaction(function () use ($user, $contactCount, $limit) {
            $quota = CampaignQuota::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$quota) {
                $quota = CampaignQuota::create([
                    'user_id' => $user->id,
                    'contacts_sent' => 0,
                    'window_starts_at' => now(),
                    'window_ends_at' => now()->addHours(SystemSetting::getCampaignQuotaWindowHours()),
                ]);
            }

            if ($quota->isWindowExpired()) {
                $quota->resetWindow();
            }

            if (($quota->contacts_sent + $contactCount) <= $limit) {
                $quota->contacts_sent += $contactCount;
                $quota->save();

                Log::info("Quota reservation SUCCESS for user {$user->id} (DB lock)", [
                    'reserved' => $contactCount,
                    'new_total' => $quota->contacts_sent,
                ]);

                Cache::put($this->getCacheKey($user->id), $quota, $this->cacheTtl);
                return true;
            }

            Log::info("Quota reservation DENIED for user {$user->id} (DB lock)", [
                'current' => $quota->contacts_sent,
                'requested' => $contactCount,
                'limit' => $limit,
            ]);
            return false;
        });
    }

    /**
     * Release reserved quota when campaign send is aborted.
     * This is used to rollback quota when WhatsApp disconnection is detected
     * after reservation but before dispatching jobs.
     */
    public function releaseReservedQuota(User $user, int $contactCount): void
    {
        Log::info("Releasing reserved quota for user {$user->id}: {$contactCount} contacts");

        if ($this->redisAvailable) {
            $this->atomicReleaseReservedQuota($user->id, $contactCount);
        } else {
            $this->databaseReleaseReservedQuota($user, $contactCount);
        }
    }

    /**
     * Atomic release using Redis DECRBY.
     */
    protected function atomicReleaseReservedQuota(int $userId, int $contactCount): void
    {
        $key = $this->getRedisKey($userId);

        try {
            // Atomic decrement in Redis
            $newTotal = Redis::decrby($key, $contactCount);

            // Ensure we don't go below 0
            if ($newTotal < 0) {
                Redis::set($key, 0);
                $newTotal = 0;
            }

            Log::info("Quota release SUCCESS for user {$userId} (atomic)", [
                'released' => $contactCount,
                'new_total' => $newTotal,
            ]);

            // Sync to DB
            $this->syncDbFromRedis($userId, $newTotal);

        } catch (\Exception $e) {
            Log::error("Redis atomicReleaseReservedQuota failed: " . $e->getMessage());
            // Fall back to database
            $user = User::find($userId);
            if ($user) {
                $this->databaseReleaseReservedQuota($user, $contactCount);
            }
        }
    }

    /**
     * Database-based quota release.
     */
    protected function databaseReleaseReservedQuota(User $user, int $contactCount): void
    {
        \DB::transaction(function () use ($user, $contactCount) {
            $quota = CampaignQuota::where('user_id', $user->id)->lockForUpdate()->first();

            if ($quota) {
                $quota->contacts_sent = max(0, $quota->contacts_sent - $contactCount);
                $quota->save();

                Log::info("Quota release SUCCESS for user {$user->id} (DB)", [
                    'released' => $contactCount,
                    'new_total' => $quota->contacts_sent,
                ]);

                Cache::put($this->getCacheKey($user->id), $quota, $this->cacheTtl);
            }
        });
    }

    /**
     * Reset Redis counter for user.
     */
    protected function resetRedisCounter(int $userId): void
    {
        if (!$this->redisAvailable)
            return;

        try {
            Redis::set($this->getRedisKey($userId), 0);
            Redis::expire($this->getRedisKey($userId), $this->cacheTtl);
        } catch (\Exception $e) {
            Log::warning("Failed to reset Redis counter: " . $e->getMessage());
        }
    }

    /**
     * Get current Redis counter value.
     */
    protected function getRedisCounter(int $userId): ?int
    {
        if (!$this->redisAvailable)
            return null;

        try {
            $value = Redis::get($this->getRedisKey($userId));
            return $value !== null ? (int) $value : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Sync Redis counter with database value.
     */
    protected function syncRedisWithDb(int $userId, int $dbValue): void
    {
        if (!$this->redisAvailable)
            return;

        try {
            Redis::set($this->getRedisKey($userId), $dbValue);
            Redis::expire($this->getRedisKey($userId), $this->cacheTtl);
        } catch (\Exception $e) {
            Log::warning("Failed to sync Redis with DB: " . $e->getMessage());
        }
    }

    /**
     * Sync database from Redis value.
     */
    protected function syncDbFromRedis(int $userId, int $redisValue): void
    {
        try {
            CampaignQuota::where('user_id', $userId)->update(['contacts_sent' => $redisValue]);

            // Update cache
            $quota = CampaignQuota::where('user_id', $userId)->first();
            if ($quota) {
                Cache::put($this->getCacheKey($userId), $quota, $this->cacheTtl);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to sync DB from Redis: " . $e->getMessage());
        }
    }

    /**
     * Get current quota status for UI display.
     */
    public function getQuotaStatus(User $user): array
    {
        // Limits removed - return unlimited status
        return [
            'limit' => PHP_INT_MAX,
            'used' => 0,
            'remaining' => PHP_INT_MAX,
            'percentage_used' => 0,
            'percentage_remaining' => 100,
            'status_color' => 'success',
            'reset_in' => 'غير محدود',
            'reset_hours' => 0,
            'reset_minutes' => 0,
            'is_window_expired' => true,
            'window_starts_at' => null,
            'window_ends_at' => null,
            'redis_available' => $this->redisAvailable,
        ];
    }

    /**
     * Force refresh quota from database (bypass cache and Redis).
     */
    public function refreshQuota(User $user): CampaignQuota
    {
        Cache::forget($this->getCacheKey($user->id));
        $quota = CampaignQuota::where('user_id', $user->id)->first();

        if ($quota && $this->redisAvailable) {
            $this->syncRedisWithDb($user->id, $quota->contacts_sent);
        }

        return $this->getQuota($user);
    }
}
