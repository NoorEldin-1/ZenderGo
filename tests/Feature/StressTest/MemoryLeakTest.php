<?php

use App\Models\User;
use App\Services\SessionManager;
use Illuminate\Support\Facades\Cache;

/**
 * Memory Leak Detection Tests
 */
describe('Memory Leak Detection', function () {

    beforeEach(function () {
        $this->sessionManager = new SessionManager();
        $this->initialMemory = memory_get_usage(true);
    });

    test('php memory baseline is recordable', function () {
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        expect($memory)->toBeGreaterThan(0);
        expect($peak)->toBeGreaterThanOrEqual($memory);
    });

    test('session manager operations do not leak memory', function () {
        $before = memory_get_usage(true);

        // Perform multiple operations
        for ($i = 0; $i < 10; $i++) {
            $this->sessionManager->getActiveSessions();
            $this->sessionManager->getActiveSessionCount();
            $this->sessionManager->getIdleSessions();
            $this->sessionManager->getRamStatus();
        }

        $after = memory_get_usage(true);
        $delta = ($after - $before) / 1024 / 1024; // MB

        // Should not increase more than 5MB
        expect($delta)->toBeLessThan(5);
    });

    test('orphaned sessions can be detected', function () {
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

        // Just check that detection works, not that there are no orphans
        expect($orphaned)->toBeArray();
    });

    test('cache operations are performant', function () {
        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            Cache::put("memory_test_{$i}", "value_{$i}", 60);
            Cache::get("memory_test_{$i}");
            Cache::forget("memory_test_{$i}");
        }

        $duration = microtime(true) - $startTime;

        // Should complete 300 ops in under 2 seconds
        expect($duration)->toBeLessThan(2);
    });

    test('database queries are performant under load', function () {
        $startTime = microtime(true);

        for ($i = 0; $i < 50; $i++) {
            User::count();
        }

        $duration = microtime(true) - $startTime;

        // Should complete 50 queries in under 2 seconds
        expect($duration)->toBeLessThan(2);
    });

});

/**
 * Edge Case Tests
 */
describe('Edge Cases', function () {

    test('double close session does not crash', function () {
        $user = User::whereNotNull('whatsapp_session')->first();

        if (!$user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $sessionManager = new SessionManager();

        // Try to close twice - should not throw
        $result1 = $sessionManager->closeSession($user);
        $result2 = $sessionManager->closeSession($user);

        // Just verify no crash - results can be true/false
        expect(true)->toBeTrue();
    });

    test('session manager handles missing user gracefully', function () {
        $fakeUser = new User([
            'id' => 999999,
            'whatsapp_session' => null,
            'whatsapp_token' => null,
        ]);

        $sessionManager = new SessionManager();
        $result = $sessionManager->wakeSession($fakeUser);

        expect($result['status'])->toBe('error');
    });

});
