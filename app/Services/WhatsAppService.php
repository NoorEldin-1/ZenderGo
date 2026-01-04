<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $token;
    protected string $secretKey;
    protected ?string $session;

    public function __construct(?string $session = null, ?string $token = null)
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
        $this->token = $token ?? config('services.whatsapp.token', '');
        $this->secretKey = config('services.whatsapp.secret_key', 'THISISMYSECURETOKEN');
        $this->session = $session ?? config('services.whatsapp.session', 'my-session');
    }

    /**
     * Set the WhatsApp session to use.
     */
    public function setSession(string $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Get the current session.
     */
    public function getSession(): string
    {
        return $this->session;
    }

    /**
     * Get the current token.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Generate a token for a new session.
     * This MUST be called before starting a new session for the first time.
     */
    public function generateToken(): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/api/{$this->session}/{$this->secretKey}/generate-token");

            $data = $response->json();
            Log::info("WhatsApp generate token response for {$this->session}", [
                'status_code' => $response->status(),
                'data' => $data
            ]);

            if (isset($data['token'])) {
                // Update the token for this service instance
                $this->token = $data['token'];
                return [
                    'success' => true,
                    'token' => $data['token'],
                ];
            }

            return [
                'success' => $response->successful(),
                'message' => $data['message'] ?? 'Token generated',
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp generate token error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء التوكن: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Start a new WhatsApp session.
     * Returns the QR code if authentication is needed.
     */
    public function startSession(): array
    {
        try {
            // First, generate token for this session
            $tokenResult = $this->generateToken();
            Log::info("Token generation result for {$this->session}", $tokenResult);

            // Use the new token if available
            $authToken = $tokenResult['token'] ?? $this->token;

            $response = Http::withToken($authToken)
                ->timeout(60)
                ->post("{$this->baseUrl}/api/{$this->session}/start-session", [
                    'webhook' => null,
                    'waitQrCode' => true,
                ]);

            $data = $response->json();
            Log::info("WhatsApp start session response for {$this->session}", [
                'status_code' => $response->status(),
                'has_qrcode' => isset($data['qrcode']),
                'has_qr' => isset($data['qr']),
                'data_keys' => array_keys($data ?? [])
            ]);

            // WPPConnect may return QR code in different fields
            $qrcode = $data['qrcode'] ?? $data['qr'] ?? $data['urlCode'] ?? null;

            if ($qrcode) {
                Log::info("QR code found for {$this->session}, length: " . strlen($qrcode));
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'qrcode',
                    'qrcode' => $qrcode,
                    'message' => null,
                ];
            }

            // Check if already connected
            $status = $data['status'] ?? '';
            if (in_array($status, ['CONNECTED', 'isLogged', 'inChat', 'PAIRING'])) {
                return [
                    'success' => true,
                    'status' => $status,
                    'qrcode' => null,
                    'message' => 'الجلسة متصلة بالفعل',
                ];
            }

            // If we got here, return what we have
            Log::warning("WhatsApp start session - no QR in response for {$this->session}", $data);
            return [
                'success' => false,
                'status' => $data['status'] ?? 'unknown',
                'qrcode' => null,
                'message' => $data['message'] ?? 'جاري بدء الجلسة...',
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("WhatsApp connection error: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'message' => 'لا يمكن الاتصال بخادم WhatsApp',
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp start session exception: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'message' => 'خطأ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get QR code for session authentication.
     */
    public function getQrCode(): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->get("{$this->baseUrl}/api/{$this->session}/qrcode-session");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'qrcode' => $data['qrcode'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'لا يوجد QR code متاح',
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp QR code error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطأ في جلب QR code',
            ];
        }
    }

    /**
     * Check connection status of the session.
     */
    public function checkConnection(): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->get("{$this->baseUrl}/api/{$this->session}/check-connection-session");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'connected' => ($data['status'] ?? false) === true || ($data['message'] ?? '') === 'Connected',
                    'status' => $data['status'] ?? 'unknown',
                    'message' => $data['message'] ?? null,
                ];
            }

            return [
                'success' => false,
                'connected' => false,
                'status' => 'disconnected',
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp check connection error: " . $e->getMessage());
            return [
                'success' => false,
                'connected' => false,
                'status' => 'error',
                'message' => 'خطأ في التحقق من الاتصال',
            ];
        }
    }

    /**
     * Close/logout from the session.
     */
    public function closeSession(): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(10)
                ->post("{$this->baseUrl}/api/{$this->session}/close-session");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsApp close session error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a text message via WhatsApp.
     */
    public function sendMessage(string $phone, string $message): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/api/{$this->session}/send-message", [
                    'phone' => $this->formatPhone($phone),
                    'message' => $message,
                    'isGroup' => false,
                    'isNewsletter' => false,
                    'isLid' => false,
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent to {$phone} using session {$this->session}");
                return true;
            }

            Log::error("WhatsApp API error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp service error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an image with caption via WhatsApp.
     */
    public function sendImage(string $phone, string $message, string $imagePath): bool
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            $filename = basename($imagePath);

            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/api/{$this->session}/send-image", [
                    'phone' => $this->formatPhone($phone),
                    'caption' => $message,
                    'isGroup' => false,
                    'isNewsletter' => false,
                    'isLid' => false,
                    'filename' => $filename,
                    'base64' => "data:{$mimeType};base64,{$imageData}",
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp image sent to {$phone} using session {$this->session}");
                return true;
            }

            Log::error("WhatsApp API error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp service error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number for WhatsApp API.
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '20' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '20')) {
            $phone = '20' . $phone;
        }

        return $phone;
    }
}
