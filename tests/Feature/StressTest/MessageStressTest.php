<?php

use App\Models\User;
use App\Services\SessionManager;
use App\Services\WhatsAppService;

/**
 * Message Sending Stress Tests
 */
describe('Message Sending', function () {

    beforeEach(function () {
        $this->user = User::whereNotNull('whatsapp_session')
            ->whereNotNull('whatsapp_token')
            ->first();
        $this->testPhone = '01552678658';
    });

    test('can send text message', function () {
        if (!$this->user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $whatsapp = new WhatsAppService($this->user->whatsapp_session, $this->user->whatsapp_token);

        // First check connection
        $connection = $whatsapp->checkConnection();
        if (!($connection['connected'] ?? false)) {
            $this->markTestSkipped('WhatsApp session not connected');
        }

        $message = "🧪 Pest Test Message - " . now()->format('Y-m-d H:i:s');
        $result = $whatsapp->sendMessage($this->testPhone, $message);

        expect($result)->toBeTrue();
    });

    test('handles invalid phone number gracefully', function () {
        if (!$this->user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $whatsapp = new WhatsAppService($this->user->whatsapp_session, $this->user->whatsapp_token);

        // Should not throw exception, just return false
        $result = $whatsapp->sendMessage('invalid', 'Test');

        expect($result)->toBeBool();
    });

    test('handles empty message', function () {
        if (!$this->user) {
            $this->markTestSkipped('No user with WhatsApp session found');
        }

        $whatsapp = new WhatsAppService($this->user->whatsapp_session, $this->user->whatsapp_token);
        $result = $whatsapp->sendMessage($this->testPhone, '');

        expect($result)->toBeBool();
    });

    test('phone number formatting works correctly', function () {
        $service = new class extends WhatsAppService {
            public function testFormatPhone(string $phone): string
            {
                return $this->formatPhone($phone);
            }
        };

        // Test various formats
        expect($service->testFormatPhone('01552678658'))->toBe('201552678658');
        expect($service->testFormatPhone('1552678658'))->toBe('201552678658');
        expect($service->testFormatPhone('201552678658'))->toBe('201552678658');
        expect($service->testFormatPhone('+201552678658'))->toBe('201552678658');
        expect($service->testFormatPhone('0155-267-8658'))->toBe('201552678658');
    });

});

/**
 * Campaign Job Tests
 */
describe('Campaign Jobs', function () {

    test('pending campaign jobs count is retrievable', function () {
        $pendingJobs = DB::table('jobs')
            ->where('payload', 'like', '%SendWhatsappCampaign%')
            ->count();

        expect($pendingJobs)->toBeGreaterThanOrEqual(0);
    });

    test('failed jobs can be counted', function () {
        $failedJobs = DB::table('failed_jobs')->count();

        expect($failedJobs)->toBeGreaterThanOrEqual(0);
    });

});
