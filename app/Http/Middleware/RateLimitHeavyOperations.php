<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limit heavy operations to prevent server overload.
 * 
 * Usage in routes:
 * Route::post('/contacts/import', ...)->middleware('rate.heavy:import');
 * Route::post('/campaigns/send', ...)->middleware('rate.heavy:campaign');
 */
class RateLimitHeavyOperations
{
    /**
     * Rate limits configuration per operation type.
     * 
     * @var array<string, array{max: int, minutes: int}>
     */
    protected array $limits = [
        'import' => ['max' => 5, 'minutes' => 60],      // 5 imports per hour
        'campaign' => ['max' => 20, 'minutes' => 60],   // 20 campaigns per hour
        'share' => ['max' => 30, 'minutes' => 60],      // 30 share requests per hour
        'bulk_delete' => ['max' => 10, 'minutes' => 60], // 10 bulk deletes per hour
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $operation = 'default'): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $limit = $this->limits[$operation] ?? ['max' => 60, 'minutes' => 1];
        $key = "rate_limit:{$operation}:{$user->id}";

        $attempts = Cache::get($key, 0);

        if ($attempts >= $limit['max']) {
            Log::warning("Rate limit exceeded for user {$user->id} on operation: {$operation}", [
                'attempts' => $attempts,
                'limit' => $limit['max'],
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->getLimitMessage($operation, $limit['minutes']),
                ], 429);
            }

            return back()->withErrors([
                'rate_limit' => $this->getLimitMessage($operation, $limit['minutes']),
            ]);
        }

        // Increment counter
        if ($attempts === 0) {
            Cache::put($key, 1, now()->addMinutes($limit['minutes']));
        } else {
            Cache::increment($key);
        }

        return $next($request);
    }

    /**
     * Get user-friendly rate limit message based on operation type.
     */
    protected function getLimitMessage(string $operation, int $minutes): string
    {
        $messages = [
            'import' => "لقد تجاوزت الحد المسموح لاستيراد جهات الاتصال. حاول مرة أخرى بعد {$minutes} دقيقة.",
            'campaign' => "لقد تجاوزت الحد المسموح لإرسال الحملات. حاول مرة أخرى بعد {$minutes} دقيقة.",
            'share' => "لقد تجاوزت الحد المسموح لطلبات المشاركة. حاول مرة أخرى بعد {$minutes} دقيقة.",
            'bulk_delete' => "لقد تجاوزت الحد المسموح للحذف المتعدد. حاول مرة أخرى بعد {$minutes} دقيقة.",
        ];

        return $messages[$operation] ?? "لقد تجاوزت الحد المسموح. حاول مرة أخرى بعد {$minutes} دقيقة.";
    }
}
