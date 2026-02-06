<?php

namespace App\Console\Commands;

use App\Models\PaymentRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:cleanup 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--receipts-days=30 : Days to keep approved/rejected payment receipts}
                            {--collage-hours=24 : Hours to keep orphaned collage images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old storage files (payment receipts and orphaned collage images)';

    /**
     * Maximum files to process per run (prevents I/O overload)
     */
    private const BATCH_SIZE = 50;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $receiptsDays = (int) $this->option('receipts-days');
        $collageHours = (int) $this->option('collage-hours');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be deleted');
            $this->newLine();
        }

        $this->info('Starting storage cleanup...');
        $this->newLine();

        // 1. Cleanup processed payment receipts
        $receiptCount = $this->cleanupPaymentReceipts($dryRun, $receiptsDays);

        // Small delay between operations to prevent I/O spike
        if (!$dryRun) {
            usleep(500000); // 0.5 seconds
        }

        // 2. Cleanup orphaned collage images
        $collageCount = $this->cleanupOrphanedCollages($dryRun, $collageHours);

        // Summary
        $this->newLine();
        $this->info('📊 Cleanup Summary:');
        $this->table(
            ['Type', 'Files Processed'],
            [
                ['Payment Receipts', $receiptCount],
                ['Orphaned Collages', $collageCount],
                ['Total', $receiptCount + $collageCount],
            ]
        );

        if ($dryRun) {
            $this->warn('⚠️  This was a dry run. Run without --dry-run to actually delete files.');
        } else {
            Log::info("Storage cleanup completed: {$receiptCount} receipts, {$collageCount} collages deleted");
        }

        return Command::SUCCESS;
    }

    /**
     * Cleanup processed payment receipts older than specified days.
     */
    private function cleanupPaymentReceipts(bool $dryRun, int $days): int
    {
        $this->info("🧾 Cleaning payment receipts older than {$days} days...");

        $receipts = PaymentRequest::whereIn('status', ['approved', 'rejected'])
            ->where('reviewed_at', '<', now()->subDays($days))
            ->whereNotNull('receipt_image')
            ->where('receipt_image', '!=', '')
            ->limit(self::BATCH_SIZE)
            ->get();

        $deleted = 0;

        foreach ($receipts as $receipt) {
            $imagePath = $receipt->receipt_image;

            if ($dryRun) {
                $this->line("  Would delete: {$imagePath}");
            } else {
                if ($receipt->deleteReceiptImage()) {
                    $receipt->update(['receipt_image' => null]);
                    $this->line("  ✓ Deleted: {$imagePath}");
                    $deleted++;
                }
            }
        }

        if ($receipts->isEmpty()) {
            $this->line('  No old payment receipts found.');
        }

        return $dryRun ? $receipts->count() : $deleted;
    }

    /**
     * Cleanup orphaned collage images older than specified hours.
     */
    private function cleanupOrphanedCollages(bool $dryRun, int $hours): int
    {
        $this->info("🖼️  Cleaning orphaned collage images older than {$hours} hours...");

        $campaignImagesPath = storage_path('app/public/campaign-images');

        if (!is_dir($campaignImagesPath)) {
            $this->line('  Campaign images directory does not exist.');
            return 0;
        }

        $files = glob($campaignImagesPath . DIRECTORY_SEPARATOR . 'collage_*.jpg');
        $cutoffTime = now()->subHours($hours)->timestamp;
        $deleted = 0;
        $wouldDelete = 0;

        foreach ($files as $file) {
            // Only delete collages older than cutoff time
            if (filemtime($file) < $cutoffTime) {
                $basename = basename($file);

                if ($dryRun) {
                    $this->line("  Would delete: {$basename}");
                    $wouldDelete++;
                } else {
                    if (@unlink($file)) {
                        $this->line("  ✓ Deleted: {$basename}");
                        $deleted++;
                    }
                }

                // Limit per run to prevent I/O overload
                if (($dryRun ? $wouldDelete : $deleted) >= self::BATCH_SIZE) {
                    $this->warn("  ⚠️  Batch limit reached. Remaining files will be processed next run.");
                    break;
                }
            }
        }

        if ($deleted === 0 && $wouldDelete === 0) {
            $this->line('  No orphaned collage images found.');
        }

        return $dryRun ? $wouldDelete : $deleted;
    }
}
