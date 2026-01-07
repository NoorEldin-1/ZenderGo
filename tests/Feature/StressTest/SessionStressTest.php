<?php

use App\Models\User;
use App\Services\SessionManager;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;

/**
 * Session Lifecycle Stress Tests
 */
describe('Session Lifecycle', function () {

    beforeEach(function () {
        $this->sessionManager = new SessionManager();
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
        $this->secretKey = config('services.whatsapp.secret_key', 'THISISMYSECURETOKEN');
    });

    test('wppconnect server is accessible', function () {
        $response = Http::timeout(5)->get("{$this->baseUrl}/api-docs");
        expect($response->status())->toBeLessThanOrEqual(404);
    });

    test('can retrieve active sessions from wppconnect', function () {
        $response = Http::timeout(10)->get("{$this->baseUrl}/api/{$this->secretKey}/show-all-sessions");
        expect($response->successful())->toBeTrue();
        expect($response->json())->toHaveKey('response');
    });

    test('session manager tracks active sessions correctly', function () {
        $sessions = $this->sessionManager->getActiveSessions();
        $count = $this->sessionManager->getActiveSessionCount();

        expect($count)->toBeGreaterThanOrEqual(0);
        expect(count($sessions))->toBe($count);
    });

    test('ram status returns valid data', function () {
        $ramStatus = $this->sessionManager->getRamStatus();

        expect($ramStatus)->toHaveKeys(['total_mb', 'used_mb', 'free_mb', 'usage_percent', 'is_critical', 'is_warning']);
        expect($ramStatus['total_mb'])->toBeGreaterThan(0);
        expect($ramStatus['usage_percent'])->toBeGreaterThanOrEqual(0);
        expect($ramStatus['usage_percent'])->toBeLessThanOrEqual(100);
    });

    test('concurrent sessions limit is enforced', function () {
        $maxSessions = 10;
        $currentCount = $this->sessionManager->getActiveSessionCount();

        expect($currentCount)->toBeLessThanOrEqual($maxSessions);
    });

    test('idle sessions detection works', function () {
        $idleSessions = $this->sessionManager->getIdleSessions();

        expect($idleSessions)->toBeArray();
    });

});

/**
 * WhatsApp Service Tests
 */
describe('WhatsApp Service', function () {

    beforeEach(function () {
        $this->user = User::whereNotNull('whatsapp_session')
            ->whereNotNull('whatsapp_token')
            ->first();
    });

    test('whatsapp service can be instantiated', function () {
        if (!$this->user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $whatsapp = new WhatsAppService($this->user->whatsapp_session, $this->user->whatsapp_token);

        expect($whatsapp)->toBeInstanceOf(WhatsAppService::class);
        expect($whatsapp->getSession())->toBe($this->user->whatsapp_session);
    });

    test('check connection returns valid response', function () {
        if (!$this->user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $whatsapp = new WhatsAppService($this->user->whatsapp_session, $this->user->whatsapp_token);
        $result = $whatsapp->checkConnection();

        expect($result)->toHaveKeys(['success', 'connected', 'status']);
    });

    test('session manager can wake session', function () {
        if (!$this->user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $sessionManager = new SessionManager();
        $result = $sessionManager->wakeSession($this->user);

        expect($result)->toHaveKeys(['status', 'service', 'message']);
        expect($result['status'])->toBeIn(['connected', 'needs_qr', 'error']);
    });

});
