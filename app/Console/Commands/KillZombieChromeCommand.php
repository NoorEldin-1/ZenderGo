<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Kill orphaned Chrome/Chromium processes that weren't cleaned up properly.
 * 
 * This handles the "Zombie Process" scenario where:
 * - WPPConnect session close API call fails
 * - Script injection failure leaves Chrome running
 * - Network timeout during session cleanup
 * 
 * Should be scheduled to run every 5 minutes.
 */
class KillZombieChromeCommand extends Command
{
    protected $signature = 'sessions:kill-zombies 
                            {--dry-run : Show which processes would be killed without actually killing them}
                            {--max-age=300 : Kill Chrome processes older than this many seconds (default: 5 minutes)}';

    protected $description = 'Kill orphaned Chrome/Chromium processes that exceed the max age threshold';

    public function handle(): int
    {
        $this->info('');
        $this->info('🧟 Checking for zombie Chrome processes...');
        $this->info('');

        $maxAge = (int) $this->option('max-age');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No processes will actually be killed');
        }

        $zombies = $this->findZombieProcesses($maxAge);

        if (empty($zombies)) {
            $this->info('✅ No zombie Chrome processes found.');
            return Command::SUCCESS;
        }

        $this->warn("Found " . count($zombies) . " zombie Chrome process(es):");

        $tableData = [];
        foreach ($zombies as $process) {
            $tableData[] = [
                $process['pid'],
                $this->formatSeconds($process['age']),
                $this->formatBytes($process['memory']),
                substr($process['command'], 0, 60) . '...',
            ];
        }

        $this->table(['PID', 'Age', 'Memory', 'Command'], $tableData);

        if ($dryRun) {
            $this->info('');
            $this->info('Would kill ' . count($zombies) . ' processes. Run without --dry-run to actually kill them.');
            return Command::SUCCESS;
        }

        $killed = 0;
        foreach ($zombies as $process) {
            if ($this->killProcess($process['pid'])) {
                $killed++;
            }
        }

        $this->info('');
        $this->info("☠️  Killed {$killed} zombie Chrome processes.");
        Log::info("Killed {$killed} zombie Chrome processes via sessions:kill-zombies");

        // Show memory reclaimed
        $this->showMemoryStatus();

        return Command::SUCCESS;
    }

    /**
     * Find Chrome processes that are older than the max age threshold.
     */
    protected function findZombieProcesses(int $maxAge): array
    {
        $zombies = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $zombies = $this->findWindowsZombies($maxAge);
        } else {
            $zombies = $this->findLinuxZombies($maxAge);
        }

        return $zombies;
    }

    /**
     * Find zombie Chrome processes on Windows.
     */
    protected function findWindowsZombies(int $maxAge): array
    {
        $zombies = [];

        // Get Chrome processes with their creation time
        $output = shell_exec('wmic process where "name=\'chrome.exe\' or name=\'chromium.exe\'" get ProcessId,CreationDate,WorkingSetSize,CommandLine /format:csv 2>&1');

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        array_shift($lines); // Remove header

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            $parts = str_getcsv($line);
            if (count($parts) < 4)
                continue;

            // CSV format: Node,CommandLine,CreationDate,ProcessId,WorkingSetSize
            $commandLine = $parts[1] ?? '';
            $creationDate = $parts[2] ?? '';
            $pid = $parts[3] ?? '';
            $memory = $parts[4] ?? 0;

            // Only target WPPConnect/Puppeteer Chrome instances
            if (
                stripos($commandLine, 'wppconnect') === false &&
                stripos($commandLine, 'puppeteer') === false &&
                stripos($commandLine, 'userDataDir') === false
            ) {
                continue;
            }

            // Parse Windows WMI datetime format: YYYYMMDDHHMMSS.MMMMMM+UUU
            if (preg_match('/^(\d{14})/', $creationDate, $matches)) {
                $startTime = strtotime(substr($matches[1], 0, 8) . 'T' . substr($matches[1], 8, 6));
                $age = time() - $startTime;

                if ($age > $maxAge) {
                    $zombies[] = [
                        'pid' => (int) $pid,
                        'age' => $age,
                        'memory' => (int) $memory,
                        'command' => $commandLine,
                    ];
                }
            }
        }

        return $zombies;
    }

    /**
     * Find zombie Chrome processes on Linux.
     */
    protected function findLinuxZombies(int $maxAge): array
    {
        $zombies = [];

        // Get Chrome/Chromium processes with their start time
        $output = shell_exec("ps -eo pid,etimes,rss,args --no-headers | grep -E '(chrome|chromium)' | grep -v grep");

        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // Format: PID ELAPSED_SECONDS RSS COMMAND...
            if (preg_match('/^\s*(\d+)\s+(\d+)\s+(\d+)\s+(.+)$/', $line, $matches)) {
                $pid = (int) $matches[1];
                $age = (int) $matches[2];
                $memory = (int) $matches[3] * 1024; // RSS is in KB
                $command = $matches[4];

                // Only target WPPConnect/Puppeteer Chrome instances
                if (
                    stripos($command, 'wppconnect') === false &&
                    stripos($command, 'puppeteer') === false &&
                    stripos($command, 'userDataDir') === false
                ) {
                    continue;
                }

                if ($age > $maxAge) {
                    $zombies[] = [
                        'pid' => $pid,
                        'age' => $age,
                        'memory' => $memory,
                        'command' => $command,
                    ];
                }
            }
        }

        return $zombies;
    }

    /**
     * Kill a process by PID.
     */
    protected function killProcess(int $pid): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $result = shell_exec("taskkill /F /PID {$pid} 2>&1");
            return stripos($result, 'SUCCESS') !== false;
        } else {
            $result = shell_exec("kill -9 {$pid} 2>&1");
            return empty($result) || stripos($result, 'no such process') === false;
        }
    }

    /**
     * Show current memory status.
     */
    protected function showMemoryStatus(): void
    {
        $this->info('');
        $this->info('💾 Current Memory Status:');

        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
            preg_match('/FreePhysicalMemory=(\d+)/', $output, $freeMatch);
            preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $totalMatch);

            $free = isset($freeMatch[1]) ? (int) $freeMatch[1] * 1024 : 0;
            $total = isset($totalMatch[1]) ? (int) $totalMatch[1] * 1024 : 0;
        } else {
            $meminfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $meminfo, $totalMatch);
            preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $availMatch);

            $total = isset($totalMatch[1]) ? (int) $totalMatch[1] * 1024 : 0;
            $free = isset($availMatch[1]) ? (int) $availMatch[1] * 1024 : 0;
        }

        $used = $total - $free;
        $usagePercent = $total > 0 ? round(($used / $total) * 100, 1) : 0;

        $this->table(['Metric', 'Value'], [
            ['Total RAM', $this->formatBytes($total)],
            ['Used RAM', $this->formatBytes($used)],
            ['Free RAM', $this->formatBytes($free)],
            ['Usage', "{$usagePercent}%"],
        ]);
    }

    /**
     * Format seconds into human readable format.
     */
    protected function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . "m";
        } else {
            return round($seconds / 3600, 1) . "h";
        }
    }

    /**
     * Format bytes into human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024) . " KB";
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576) . " MB";
        } else {
            return round($bytes / 1073741824, 1) . " GB";
        }
    }
}
