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

            // GHOST SESSION FIX: Treat CLOSED/DISCONNECTED as "Needs QR"
            // If the Node.js server explicitly reports CLOSED (due to max QR attempts or logout),
            // tell SessionManager to stop retrying and ask for re-auth.
            $status = strtoupper($data['status'] ?? 'UNKNOWN');
            if (in_array($status, ['CLOSED', 'DISCONNECTED', 'NOTLOGGED', 'QRCODE'])) {
                return [
                    'success' => true, // Sent successfully, just needs auth
                    'status' => 'qrcode', // Force SessionManager to trigger "needs_qr" flow
                    'qrcode' => null, // Might be null if max attempts reached, but flow is same
                    'message' => 'الجلسة مغلقة. يرجى إعادة الربط.',
                ];
            }

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
     * Uses status-session endpoint which returns JSON with qrcode field.
     */
    public function getQrCode(): array
    {
        try {
            // Use status-session endpoint which returns JSON with qrcode as base64
            // (qrcode-session returns binary PNG image which doesn't work for our use case)
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->get("{$this->baseUrl}/api/{$this->session}/status-session");

            Log::info("QR code API response for {$this->session}", [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 300),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // WPPConnect returns qrcode as base64 data URL
                $qrcode = $data['qrcode'] ?? null;

                // Also check urlcode which might need conversion
                if (!$qrcode && !empty($data['urlcode'])) {
                    // urlcode is raw text, we can return it for QR generation
                    Log::info("Got urlcode for {$this->session}, converting to QR");
                    $qrcode = $data['urlcode'];
                }

                if ($qrcode) {
                    Log::info("QR code found for {$this->session}, length: " . strlen($qrcode));
                    return [
                        'success' => true,
                        'qrcode' => $qrcode,
                        'status' => $data['status'] ?? 'qrcode',
                    ];
                }

                // Check if connected (no QR needed)
                $status = strtoupper($data['status'] ?? '');
                if (in_array($status, ['CONNECTED', 'ISLOGGED', 'INCHAT'])) {
                    return [
                        'success' => true,
                        'connected' => true,
                        'status' => $status,
                        'message' => 'الجلسة متصلة بالفعل',
                    ];
                }

                Log::warning("QR code API returned success but no QR code for {$this->session}", $data);
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

                // PAIRED LOGIC: Treat 'PAIRED' as connected (sleeping session)
                $message = $data['message'] ?? '';
                $isPaired = ($message === 'PAIRED');

                return [
                    'connected' => ($data['status'] ?? false) || $isPaired,
                    'message' => $message,
                    'status' => $isPaired ? 'PAIRED' : ($data['status'] ? 'CONNECTED' : 'DISCONNECTED')
                ];
            }

            return [
                'connected' => false,
                'message' => 'Failed to connect to WhatsApp server'
            ];
        } catch (\Exception $e) {
            Log::error("Connection check failed: " . $e->getMessage());
            return [
                'connected' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Deep connection check - validates actual message sending capability.
     * This performs a thorough check to ensure the session can actually send messages.
     * 
     * Returns array with:
     * - 'connected': bool - whether session is truly connected
     * - 'reason': string - failure reason if not connected
     * - 'status': string - session status
     * - 'message': string - human readable message
     * - 'needs_reauth': bool - whether user needs to re-scan QR code
     */
    public function deepConnectionCheck(): array
    {
        try {
            // Step 1: Basic connection check
            $basicCheck = $this->checkConnection();
            if (!($basicCheck['connected'] ?? false)) {
                return [
                    'connected' => false,
                    'reason' => 'basic_check_failed',
                    'status' => $basicCheck['status'] ?? 'disconnected',
                    'message' => 'الجلسة غير متصلة',
                    'needs_reauth' => true,
                ];
            }

            // Step 2: Get detailed session status
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->get("{$this->baseUrl}/api/{$this->session}/status-session");

            if (!$response->successful()) {
                $statusCode = $response->status();

                // 404 means session doesn't exist
                if ($statusCode === 404) {
                    return [
                        'connected' => false,
                        'reason' => 'session_not_found',
                        'status' => 'NOT_FOUND',
                        'message' => 'الجلسة غير موجودة. يرجى إعادة الربط.',
                        'needs_reauth' => true,
                    ];
                }

                return [
                    'connected' => false,
                    'reason' => 'status_check_failed',
                    'status' => 'error',
                    'message' => 'فشل التحقق من حالة الجلسة',
                    'needs_reauth' => false,
                ];
            }

            $data = $response->json();
            $status = strtoupper($data['status'] ?? '');
            $qrcode = $data['qrcode'] ?? null;

            Log::info("Deep connection check for session {$this->session}", [
                'status' => $status,
                'has_qrcode' => !empty($qrcode),
            ]);

            // IMPORTANT: Check for valid connected states FIRST before checking QR code
            // WPPConnect sometimes returns QR code data even when session is connected
            $validStates = ['CONNECTED', 'ISLOGGED', 'INCHAT', 'SYNCING'];
            if (in_array($status, $validStates)) {
                // Session is connected - ignore any QR code data
                return [
                    'connected' => true,
                    'reason' => null,
                    'status' => $status,
                    'message' => 'الجلسة متصلة وجاهزة للإرسال',
                    'needs_reauth' => false,
                ];
            }

            // Only check for QR code if status is explicitly QRCODE (not just if QR data exists)
            if ($status === 'QRCODE') {
                return [
                    'connected' => false,
                    'reason' => 'needs_qr',
                    'status' => 'QRCODE',
                    'message' => 'تم قطع اتصال WhatsApp من الموبايل. يرجى إعادة مسح الـ QR Code.',
                    'needs_reauth' => true,
                ];
            }

            // Check for closed/not logged states
            if (in_array($status, ['CLOSED', 'NOTLOGGED', 'DISCONNECTED', 'UNPAIRED', 'CONFLICT'])) {
                return [
                    'connected' => false,
                    'reason' => 'session_closed',
                    'status' => $status,
                    'message' => 'الجلسة مغلقة. يرجى إعادة الربط.',
                    'needs_reauth' => true,
                ];
            }

            // Starting/transitional states - might need to wait
            if (in_array($status, ['STARTING', 'INITIALIZING', 'OPENING', 'PAIRING'])) {
                // Wait a bit and check again
                sleep(2);
                $retryResponse = Http::withToken($this->token)
                    ->timeout(15)
                    ->get("{$this->baseUrl}/api/{$this->session}/status-session");

                if ($retryResponse->successful()) {
                    $retryData = $retryResponse->json();
                    $retryStatus = strtoupper($retryData['status'] ?? '');

                    if (in_array($retryStatus, $validStates)) {
                        return [
                            'connected' => true,
                            'reason' => null,
                            'status' => $retryStatus,
                            'message' => 'الجلسة متصلة وجاهزة للإرسال',
                            'needs_reauth' => false,
                        ];
                    }

                    if ($retryStatus === 'QRCODE' || !empty($retryData['qrcode'])) {
                        return [
                            'connected' => false,
                            'reason' => 'needs_qr',
                            'status' => 'QRCODE',
                            'message' => 'تم قطع اتصال WhatsApp من الموبايل. يرجى إعادة مسح الـ QR Code.',
                            'needs_reauth' => true,
                        ];
                    }
                }

                return [
                    'connected' => false,
                    'reason' => 'session_starting',
                    'status' => $status,
                    'message' => 'الجلسة قيد البدء. حاول مرة أخرى.',
                    'needs_reauth' => false,
                ];
            }

            // Unknown state - treat as error
            Log::warning("Unknown session status: {$status}", ['session' => $this->session]);
            return [
                'connected' => false,
                'reason' => 'unknown_state',
                'status' => $status,
                'message' => 'حالة الجلسة غير معروفة',
                'needs_reauth' => true,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Deep connection check - connection failed: " . $e->getMessage());
            return [
                'connected' => false,
                'reason' => 'server_unreachable',
                'status' => 'error',
                'message' => 'لا يمكن الاتصال بخادم WhatsApp',
                'needs_reauth' => false,
            ];
        } catch (\Exception $e) {
            Log::error("Deep connection check failed: " . $e->getMessage());
            return [
                'connected' => false,
                'reason' => 'exception',
                'status' => 'error',
                'message' => 'خطأ في التحقق من الاتصال',
                'needs_reauth' => false,
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
        $result = $this->sendMessageWithVerification($phone, $message);
        return $result['success'] ?? false;
    }

    /**
     * Send a text message via WhatsApp with detailed verification.
     * Returns detailed result including delivery status and disconnection detection.
     */
    public function sendMessageWithVerification(string $phone, string $message): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(30)
                ->post("{$this->baseUrl}/api/{$this->session}/send-message", [
                    'phone' => $this->formatPhone($phone),
                    'message' => $message,
                    'isGroup' => false,
                    'isNewsletter' => false,
                    'isLid' => false,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $responseData = $data['response'] ?? $data;

                Log::info("WhatsApp message sent to {$phone}", [
                    'session' => $this->session,
                    'response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            }

            // Handle error responses
            $statusCode = $response->status();
            $body = $response->json() ?? [];
            $bodyMessage = $body['message'] ?? '';

            Log::error("WhatsApp API error", [
                'phone' => $phone,
                'status' => $statusCode,
                'body' => $body,
            ]);

            // Check for disconnection indicators
            if (
                $statusCode === 404 ||
                stripos($bodyMessage, 'Disconnected') !== false ||
                stripos($bodyMessage, 'não está ativa') !== false ||
                stripos($bodyMessage, 'not active') !== false
            ) {
                return [
                    'success' => false,
                    'reason' => 'disconnected',
                    'message' => 'الجلسة مفصولة',
                    'needs_reauth' => true,
                ];
            }

            return [
                'success' => false,
                'reason' => 'api_error',
                'status_code' => $statusCode,
                'message' => $bodyMessage ?: 'خطأ في الإرسال',
                'needs_reauth' => false,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("WhatsApp connection error: " . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'connection_error',
                'message' => 'لا يمكن الاتصال بخادم WhatsApp',
                'needs_reauth' => false,
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp service error: " . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'exception',
                'message' => $e->getMessage(),
                'needs_reauth' => false,
            ];
        }
    }

    /**
     * Send an image with caption via WhatsApp.
     */
    public function sendImage(string $phone, string $message, string $imagePath): bool
    {
        $result = $this->sendImageWithVerification($phone, $message, $imagePath);
        return $result['success'] ?? false;
    }

    /**
     * Send an image with caption via WhatsApp with detailed verification.
     */
    public function sendImageWithVerification(string $phone, string $message, string $imagePath): array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            $filename = basename($imagePath);

            $response = Http::withToken($this->token)
                ->timeout(60) // Longer timeout for images
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
                $data = $response->json();
                $responseData = $data['response'] ?? $data;

                Log::info("WhatsApp image sent to {$phone}", [
                    'session' => $this->session,
                ]);

                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            }

            // Handle error responses
            $statusCode = $response->status();
            $body = $response->json() ?? [];
            $bodyMessage = $body['message'] ?? '';

            Log::error("WhatsApp API error (image)", [
                'phone' => $phone,
                'status' => $statusCode,
                'body' => $body,
            ]);

            // Check for disconnection indicators
            if (
                $statusCode === 404 ||
                stripos($bodyMessage, 'Disconnected') !== false ||
                stripos($bodyMessage, 'não está ativa') !== false ||
                stripos($bodyMessage, 'not active') !== false
            ) {
                return [
                    'success' => false,
                    'reason' => 'disconnected',
                    'message' => 'الجلسة مفصولة',
                    'needs_reauth' => true,
                ];
            }

            return [
                'success' => false,
                'reason' => 'api_error',
                'status_code' => $statusCode,
                'message' => $bodyMessage ?: 'خطأ في إرسال الصورة',
                'needs_reauth' => false,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("WhatsApp connection error: " . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'connection_error',
                'message' => 'لا يمكن الاتصال بخادم WhatsApp',
                'needs_reauth' => false,
            ];
        } catch (\Exception $e) {
            Log::error("WhatsApp service error: " . $e->getMessage());
            return [
                'success' => false,
                'reason' => 'exception',
                'message' => $e->getMessage(),
                'needs_reauth' => false,
            ];
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
