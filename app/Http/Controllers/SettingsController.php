<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show the settings form.
     */
    public function index()
    {
        $user = Auth::user();

        // Auto-generate session name if not exists
        if (!$user->whatsapp_session) {
            $user->whatsapp_session = 'user-' . $user->id;
            $user->save();
        }

        // Check WhatsApp connection status
        $whatsapp = new WhatsAppService($user->whatsapp_session);
        $connectionStatus = $whatsapp->checkConnection();

        return view('settings.index', [
            'user' => $user,
            'isConnected' => $connectionStatus['connected'] ?? false,
            'connectionStatus' => $connectionStatus,
        ]);
    }

    /**
     * Start WhatsApp session and get QR code.
     */
    public function startSession()
    {
        $user = Auth::user();

        // Auto-generate session name if not exists
        if (!$user->whatsapp_session) {
            $user->whatsapp_session = 'user-' . $user->id;
            $user->save();
        }

        Log::info("Starting WhatsApp session for user {$user->id}: {$user->whatsapp_session}");

        $whatsapp = new WhatsAppService($user->whatsapp_session);

        // First generate token and save it
        $tokenResult = $whatsapp->generateToken();
        if (!empty($tokenResult['token'])) {
            $user->whatsapp_token = $tokenResult['token'];
            $user->save();
            Log::info("Saved WhatsApp token for user {$user->id}");
        }

        $result = $whatsapp->startSession();

        Log::info("Start session result for user {$user->id}", $result);

        // If we got a QR code, great!
        if (!empty($result['qrcode'])) {
            return response()->json($result);
        }

        // If no QR code, try to get it separately after a short delay
        if ($result['status'] !== 'CONNECTED') {
            usleep(1000000); // Wait 1 second
            $qrResult = $whatsapp->getQrCode();
            Log::info("QR code fetch result for user {$user->id}", $qrResult);

            if (!empty($qrResult['qrcode'])) {
                $result['qrcode'] = $qrResult['qrcode'];
                $result['success'] = true;
            }
        }

        return response()->json($result);
    }

    /**
     * Get QR code for current session.
     */
    public function getQrCode()
    {
        $user = Auth::user();

        if (!$user->whatsapp_session) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد جلسة نشطة',
            ]);
        }

        $whatsapp = new WhatsAppService($user->whatsapp_session);
        $result = $whatsapp->getQrCode();

        return response()->json($result);
    }

    /**
     * Check connection status.
     */
    public function checkConnection()
    {
        $user = Auth::user();

        if (!$user->whatsapp_session) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'لا توجد جلسة',
            ]);
        }

        $whatsapp = new WhatsAppService($user->whatsapp_session);
        $result = $whatsapp->checkConnection();

        return response()->json($result);
    }

    /**
     * Disconnect WhatsApp session.
     */
    public function disconnect()
    {
        $user = Auth::user();

        if ($user->whatsapp_session) {
            $whatsapp = new WhatsAppService($user->whatsapp_session);
            $whatsapp->closeSession();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم قطع الاتصال بنجاح',
        ]);
    }
}
