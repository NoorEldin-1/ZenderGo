<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $session;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:21465');
        $this->session = config('services.whatsapp.session', 'my-session');
        $this->token = config('services.whatsapp.token', '');
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
                Log::info("WhatsApp message sent to {$phone}");
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
            // Read the image and convert to base64
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
                Log::info("WhatsApp image sent to {$phone}");
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
     * Automatically adds Egypt country code (20) for local numbers.
     */
    protected function formatPhone(string $phone): string
    {
        // Remove spaces, dashes, parentheses, and plus sign
        $phone = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        // If number starts with 0, remove it and add Egypt code
        if (str_starts_with($phone, '0')) {
            $phone = '20' . substr($phone, 1);
        }
        // If number doesn't start with 20 (Egypt code), add it
        elseif (!str_starts_with($phone, '20')) {
            $phone = '20' . $phone;
        }

        return $phone;
    }
}
