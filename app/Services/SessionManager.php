<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * SessionManager - Centralized WhatsApp session lifecycle management
 * 
 * This service implements the "aggressive" approach:
 * - Sessions only exist during active campaign sending
 * - Sessions are closed immediately after campaign completion
 * - Prevents RAM accumulation from idle sessions
 */
class SessionManager
{
    /**
     * Cache key prefix for session tracking
     */
    protected const CACHE_PREFIX = 'wpp_session:';

    /**
     * Maximum concurrent sessions allowed (optimized for 4GB VPS)
     * Each session uses 200-500 MB RAM
     */
    public const MAX_CONCURRENT_SESSIONS = 3;

    /**
     * Session idle timeout in seconds (aggressive cleanup for RAM efficiency)
     */
    protected const IDLE_TIMEOUT = 60; // 1 minute - aggressive cleanup for RAM efficiency

    /**
     * RAM usage percentage threshold - don't create new sessions above this
     */
    protected const RAM_THRESHOLD_PERCENT = 80;

    /**
     * Check if a user's session is already active and connected.
     * This is a LIGHTWEIGHT check that does NOT attempt to start the session.
     * Use this for campaign batch jobs to avoid race conditions.
     * 
     * @return array with 'active' => bool, 'service' => WhatsAppService|null
     */
    public function isSessionActive(User $user): array
    {
        if (!$user->whatsapp_session || !$user->whatsapp_token) {
            return ['active' => false, 'service' => null, 'reason' => 'no_credentials'];
        }

        // Check if we have this session tracked as active in cache
        $cacheKey = self::CACHE_PREFIX . $user->id;
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return ['active' => false, 'service' => null, 'reason' => 'not_tracked'];
        }

        // Create service and do a quick connection check (no start attempt)
        $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
        $connectionStatus = $whatsapp->checkConnection();

        if ($connectionStatus['connected'] ?? false) {
            // Update last activity
            $this->markSessionActive($user->id, $user->whatsapp_session);
            return ['active' => true, 'service' => $whatsapp, 'reason' => null];
        }

        return ['active' => false, 'service' => null, 'reason' => 'disconnected'];
    }

    /**
     * Wake (start) a user's WhatsApp session for sending.
     * 
     * Returns array with:
     * - 'status': 'connected', 'needs_qr' (phone disconnected), or 'error'
     * - 'service': WhatsAppService instance if connected, null otherwise
     * - 'message': Human-readable message
     */
    public function wakeSession(User $user): array
    {
        if (!$user->whatsapp_session || !$user->whatsapp_token) {
            Log::warning("Cannot wake session for user {$user->id}: missing session or token");
            return [
                'status' => 'error',
                'service' => null,
                'message' => 'لا توجد جلسة WhatsApp مرتبطة بحسابك.',
            ];
        }

        $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);

        // Check if session is already connected
        $connectionStatus = $whatsapp->checkConnection();

        if ($connectionStatus['connected'] ?? false) {
            $this->markSessionActive($user->id, $user->whatsapp_session);
            $user->update(['session_state' => 'active']);
            Log::info("Session already connected for user {$user->id}");
            return [
                'status' => 'connected',
                'service' => $whatsapp,
                'message' => 'الجلسة متصلة.',
            ];
        }

        // Check RAM before creating new session (PRODUCTION ONLY)
        // Skip RAM check in development - dev machines have high memory usage from IDEs, browsers, etc.
        if (app()->environment('production')) {
            $ramStatus = $this->getRamStatus();
            if ($ramStatus['usage_percent'] >= self::RAM_THRESHOLD_PERCENT) {
                Log::warning("RAM usage too high ({$ramStatus['usage_percent']}%), forcing cleanup before new session");
                $this->forceCloseOldestSession();

                // Check again after cleanup
                $ramStatus = $this->getRamStatus();
                if ($ramStatus['usage_percent'] >= self::RAM_THRESHOLD_PERCENT) {
                    Log::error("RAM still critical after cleanup, rejecting new session");
                    return [
                        'status' => 'error',
                        'service' => null,
                        'message' => 'الذاكرة محملة بشكل كبير. حاول مرة أخرى لاحقاً.',
                    ];
                }
            }
        }

        // Check concurrent session limit
        if ($this->getActiveSessionCount() >= self::MAX_CONCURRENT_SESSIONS) {
            Log::warning("Max concurrent sessions reached (" . self::MAX_CONCURRENT_SESSIONS . "), forcing cleanup");
            $this->forceCloseOldestSession();
        }

        // Try to start the session (with retry for just-closed sessions)
        Log::info("Waking session for user {$user->id}: {$user->whatsapp_session}");

        $maxRetries = 3;
        $retryDelay = 2; // seconds between retries

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $result = $whatsapp->startSession();
            $status = $result['status'] ?? '';
            $upperStatus = strtoupper($status);

            Log::debug("Wake attempt {$attempt} for user {$user->id}: status={$status}");

            // Handle "STARTING" state - wait and check connection
            if (in_array($upperStatus, ['STARTING', 'INITIALIZING', 'OPENING'])) {
                Log::info("Session starting for user {$user->id}, waiting...");
                sleep(3); // Wait for session to start

                // Check connection again
                $connectionStatus = $whatsapp->checkConnection();
                if ($connectionStatus['connected'] ?? false) {
                    $this->markSessionActive($user->id, $user->whatsapp_session);
                    $user->update(['session_state' => 'active']);
                    Log::info("Session woken after wait for user {$user->id}");
                    return [
                        'status' => 'connected',
                        'service' => $whatsapp,
                        'message' => 'تم تفعيل الجلسة.',
                    ];
                }
            }

            // Check if session needs QR code (means phone disconnected)
            if (!empty($result['qrcode']) || $upperStatus === 'QRCODE') {
                Log::warning("User {$user->id} needs to re-scan QR code - phone disconnected");
                return [
                    'status' => 'needs_qr',
                    'service' => null,
                    'message' => 'تم قطع اتصال WhatsApp من الموبايل. يرجى إعادة الربط.',
                ];
            }

            // Check for successful connection states
            if (in_array($upperStatus, ['CONNECTED', 'ISLOGGED', 'INCHAT', 'SYNCING'])) {
                $this->markSessionActive($user->id, $user->whatsapp_session);
                $user->update(['session_state' => 'active']);
                Log::info("Session woken successfully for user {$user->id}");
                return [
                    'status' => 'connected',
                    'service' => $whatsapp,
                    'message' => 'تم تفعيل الجلسة.',
                ];
            }

            // If not last attempt, wait and retry (session might be in cleanup state)
            if ($attempt < $maxRetries) {
                Log::info("Session wake attempt {$attempt} failed for user {$user->id}, retrying in {$retryDelay}s...");
                sleep($retryDelay);
            }
        }

        // Final connection check before giving up
        $finalCheck = $whatsapp->checkConnection();
        if ($finalCheck['connected'] ?? false) {
            $this->markSessionActive($user->id, $user->whatsapp_session);
            $user->update(['session_state' => 'active']);
            return [
                'status' => 'connected',
                'service' => $whatsapp,
                'message' => 'تم تفعيل الجلسة.',
            ];
        }

        Log::warning("Failed to wake session for user {$user->id} after {$maxRetries} attempts", $result ?? []);
        return [
            'status' => 'error',
            'service' => null,
            'message' => 'فشل في بدء الجلسة. حاول مرة أخرى.',
        ];
    }

    /**
     * Close a user's WhatsApp session to free RAM.
     */
    public function closeSession(User $user): bool
    {
        if (!$user->whatsapp_session || !$user->whatsapp_token) {
            return false;
        }

        $whatsapp = new WhatsAppService($user->whatsapp_session, $user->whatsapp_token);

        Log::info("Closing session for user {$user->id}: {$user->whatsapp_session}");

        $success = $whatsapp->closeSession();

        if ($success) {
            $this->removeSessionTracking($user->id);
            $user->update(['session_state' => 'sleeping']);
            Log::info("Session closed successfully for user {$user->id}");
        } else {
            Log::warning("Failed to close session for user {$user->id}");
        }

        return $success;
    }

    /**
     * Close session by session name (for cleanup scheduler).
     */
    public function closeSessionByName(string $sessionName, string $token): bool
    {
        $whatsapp = new WhatsAppService($sessionName, $token);
        return $whatsapp->closeSession();
    }

    /**
     * Mark a session as active (update last activity timestamp).
     */
    public function markSessionActive(int $userId, string $sessionName): void
    {
        $cacheKey = self::CACHE_PREFIX . $userId;
        Cache::put($cacheKey, [
            'session_name' => $sessionName,
            'last_activity' => now()->timestamp,
            'user_id' => $userId,
        ], self::IDLE_TIMEOUT + 60); // Cache slightly longer than timeout

        // Add to session list for tracking
        $this->addToSessionList($userId);
    }

    /**
     * Get all tracked active sessions.
     */
    public function getActiveSessions(): array
    {
        // We use a list to track all active session keys
        $sessionKeys = Cache::get(self::CACHE_PREFIX . 'list', []);
        $sessions = [];

        foreach ($sessionKeys as $userId) {
            $data = Cache::get(self::CACHE_PREFIX . $userId);
            if ($data) {
                $sessions[$userId] = $data;
            }
        }

        return $sessions;
    }

    /**
     * Get count of active sessions.
     */
    public function getActiveSessionCount(): int
    {
        return count($this->getActiveSessions());
    }

    /**
     * Get idle sessions (inactive for longer than timeout).
     */
    public function getIdleSessions(): array
    {
        $sessions = $this->getActiveSessions();
        $now = now()->timestamp;
        $idle = [];

        foreach ($sessions as $userId => $data) {
            if (($now - $data['last_activity']) > self::IDLE_TIMEOUT) {
                $idle[$userId] = $data;
            }
        }

        return $idle;
    }

    /**
     * Force close the oldest idle session to make room for new ones.
     * IMPORTANT: Respects active campaigns - won't close sessions that are mid-campaign.
     */
    public function forceCloseOldestSession(): bool
    {
        $sessions = $this->getActiveSessions();

        if (empty($sessions)) {
            return false;
        }

        // Filter out sessions with active campaigns - don't interrupt them!
        $closeable = array_filter($sessions, function ($data) {
            $userId = $data['user_id'];
            return !Cache::has("campaign_active:{$userId}");
        });

        if (empty($closeable)) {
            Log::warning("All active sessions have campaigns in progress, cannot force-close any");
            return false;
        }

        // Sort by last activity (oldest first)
        uasort($closeable, fn($a, $b) => $a['last_activity'] <=> $b['last_activity']);

        $oldest = array_key_first($closeable);
        $user = User::find($oldest);

        if ($user) {
            Log::info("Force-closing oldest idle session for user {$user->id}");
            return $this->closeSession($user);
        }

        return false;
    }

    /**
     * Close all idle sessions.
     */
    public function closeIdleSessions(): int
    {
        $idleSessions = $this->getIdleSessions();
        $closed = 0;

        foreach ($idleSessions as $userId => $data) {
            $user = User::find($userId);
            if ($user && $this->closeSession($user)) {
                $closed++;
            }
        }

        Log::info("Closed {$closed} idle sessions");
        return $closed;
    }

    /**
     * Remove session from tracking.
     */
    protected function removeSessionTracking(int $userId): void
    {
        Cache::forget(self::CACHE_PREFIX . $userId);

        // Update the list of active sessions
        $sessionKeys = Cache::get(self::CACHE_PREFIX . 'list', []);
        $sessionKeys = array_filter($sessionKeys, fn($id) => $id !== $userId);
        Cache::put(self::CACHE_PREFIX . 'list', array_values($sessionKeys), 3600);
    }

    /**
     * Add session to tracking list.
     */
    protected function addToSessionList(int $userId): void
    {
        $sessionKeys = Cache::get(self::CACHE_PREFIX . 'list', []);
        if (!in_array($userId, $sessionKeys)) {
            $sessionKeys[] = $userId;
            Cache::put(self::CACHE_PREFIX . 'list', $sessionKeys, 3600);
        }
    }

    /**
     * Get RAM usage status.
     */
    public function getRamStatus(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: use wmic
            $output = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
            preg_match('/FreePhysicalMemory=(\d+)/', $output, $freeMatch);
            preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $totalMatch);

            $free = isset($freeMatch[1]) ? (int) $freeMatch[1] * 1024 : 0; // Convert KB to bytes
            $total = isset($totalMatch[1]) ? (int) $totalMatch[1] * 1024 : 0;
        } else {
            // Linux: read from /proc/meminfo
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availMatch);

            $total = isset($totalMatch[1]) ? (int) $totalMatch[1] * 1024 : 0;
            $free = isset($availMatch[1]) ? (int) $availMatch[1] * 1024 : 0;
        }

        $used = $total - $free;
        $usagePercent = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        return [
            'total_mb' => round($total / 1024 / 1024),
            'used_mb' => round($used / 1024 / 1024),
            'free_mb' => round($free / 1024 / 1024),
            'usage_percent' => $usagePercent,
            'is_critical' => $usagePercent > 85,
            'is_warning' => $usagePercent > 70,
        ];
    }
}
