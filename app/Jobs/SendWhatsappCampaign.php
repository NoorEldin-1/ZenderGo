<?php

namespace App\Jobs;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappCampaign implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $phone,
        public string $message,
        public ?string $imagePath = null
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsapp): void
    {
        Log::info("Sending WhatsApp campaign to {$this->phone}");

        $success = false;

        if ($this->imagePath && file_exists($this->imagePath)) {
            // Send image with caption
            $success = $whatsapp->sendImage($this->phone, $this->message, $this->imagePath);
        } else {
            // Send text message only
            $success = $whatsapp->sendMessage($this->phone, $this->message);
        }

        if (!$success) {
            Log::warning("Failed to send campaign to {$this->phone}");
            // Job will be retried due to failure
            $this->fail(new \Exception("Failed to send message to {$this->phone}"));
        }

        Log::info("Successfully sent campaign to {$this->phone}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Campaign job failed for {$this->phone}: " . $exception->getMessage());
    }
}
