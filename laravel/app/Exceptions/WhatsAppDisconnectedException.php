<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when WhatsApp session is disconnected or invalid.
 * This exception is used to gracefully handle cases where:
 * - User logs out from WhatsApp on mobile while website is open
 * - Session expires due to inactivity
 * - WPPConnect server connection is lost
 */
class WhatsAppDisconnectedException extends Exception
{
    protected string $sessionName;
    protected ?string $userId;

    public function __construct(
        string $message = 'جلسة WhatsApp غير متصلة. يرجى إعادة ربط حسابك.',
        string $sessionName = '',
        ?string $userId = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->sessionName = $sessionName;
        $this->userId = $userId;
    }

    /**
     * Get the session name that was disconnected.
     */
    public function getSessionName(): string
    {
        return $this->sessionName;
    }

    /**
     * Get the user ID associated with the disconnected session.
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * Report the exception for logging.
     */
    public function report(): void
    {
        \Illuminate\Support\Facades\Log::warning('WhatsApp session disconnected', [
            'session' => $this->sessionName,
            'user_id' => $this->userId,
            'message' => $this->getMessage(),
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'whatsapp_disconnected',
                'message' => $this->getMessage(),
                'redirect' => route('login.reconnect'),
            ], 401);
        }

        return redirect()->route('login.reconnect')
            ->with('warning', $this->getMessage());
    }
}
