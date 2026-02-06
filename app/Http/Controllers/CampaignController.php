<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappCampaign;
use App\Services\CampaignQuotaService;
use App\Services\ImageCollageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CampaignController extends Controller
{
    protected CampaignQuotaService $quotaService;

    public function __construct(CampaignQuotaService $quotaService)
    {
        $this->quotaService = $quotaService;
    }

    /**
     * Show the campaign creation form.
     */
    public function create(Request $request)
    {
        // Increase execution time for this request as session status checks can be slow
        set_time_limit(180);

        if ($request->ajax()) {
            $query = Auth::user()->contacts();

            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // ====== LAST CONTACTED FILTER ======
            if ($request->filled('contact_filter')) {
                $filter = $request->contact_filter;

                if ($filter === 'featured') {
                    $query->where('is_featured', true);
                } elseif ($filter === 'normal') {
                    $query->where('is_featured', false);
                } elseif ($filter === 'never') {
                    // Never contacted - last_sent_at is NULL
                    $query->whereNull('last_sent_at');
                } elseif ($filter === 'range' && $request->filled(['date_from', 'date_to'])) {
                    // Contacted within date range
                    try {
                        $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                        $dateTo = Carbon::parse($request->date_to)->endOfDay();
                        $query->whereBetween('last_sent_at', [$dateFrom, $dateTo]);
                    } catch (\Exception $e) {
                        // Invalid date format - ignore filter
                    }
                }
            }

            $contacts = $query->latest()->paginate(10);

            return response()->json($contacts);
        }

        // Get quota status for display
        $quotaStatus = $this->quotaService->getQuotaStatus(Auth::user());

        return view('campaigns.create', compact('quotaStatus'));
    }

    /**
     * Get current quota status via AJAX.
     */
    public function quotaStatus(Request $request)
    {
        $quotaStatus = $this->quotaService->getQuotaStatus(Auth::user());
        return response()->json($quotaStatus);
    }

    /**
     * Send campaign to selected contacts with throttling.
     */
    public function send(Request $request)
    {
        // Increase execution time for campaign sending validation
        set_time_limit(180);

        $user = Auth::user();

        // BLOCKER RULE: Check if user has an active campaign
        if (Cache::has("campaign_active:{$user->id}")) {
            return back()->withErrors([
                'campaign' => 'يرجى انتظار انتهاء الحملة الحالية قبل إرسال دفعة جديدة.'
            ])->withInput();
        }

        // ====== REDIS ATOMIC LOCK - THUNDERING HERD PROTECTION ======
        // Acquire lock for 10 seconds to prevent duplicate form submissions
        $lock = Cache::lock("campaign_send_lock:{$user->id}", 10);

        if (!$lock->get()) {
            return back()->withErrors([
                'campaign' => 'جاري معالجة طلب الإرسال، يرجى الانتظار.'
            ])->withInput();
        }

        try {
            $validated = $request->validate([
                'contacts' => 'required|array|min:1|max:10',
                'contacts.*' => 'exists:contacts,id',
                'message' => 'required|string|max:4096',
                'images' => 'nullable|array|max:5', // Max 5 images
                'images.*' => 'image|max:5120', // Max 5MB each
            ], [
                'contacts.max' => 'عفواً، لا يمكن إرسال الحملة لأكثر من 10 مستلم في المرة الواحدة.',
                'images.max' => 'عفواً، الحد الأقصى هو 5 صور فقط.',
                'images.*.max' => 'عفواً، حجم كل صورة يجب ألا يتجاوز 5MB.',
            ]);

            $contactCount = count($validated['contacts']);

            // ATOMIC quota reservation - prevents race conditions
            // This atomically checks AND reserves the quota in one operation
            if (!$this->quotaService->reserveQuota($user, $contactCount)) {
                $status = $this->quotaService->getQuotaStatus($user);
                $remaining = $status['remaining'];
                $resetIn = $status['reset_in'];

                $errorMessage = $remaining === 0
                    ? "تجاوزت الحد المسموح للإرسال. الكوتا تتجدد بعد {$resetIn}."
                    : "لا يمكنك إرسال لـ {$contactCount} مستلم. متبقي لك {$remaining} رسالة فقط. الكوتا تتجدد بعد {$resetIn}.";

                return back()->withErrors(['quota' => $errorMessage])->withInput();
            }

            // Get selected contacts that belong to the user
            $contacts = $user->contacts()
                ->whereIn('id', $validated['contacts'])
                ->get();

            if ($contacts->isEmpty()) {
                return back()->withErrors(['contacts' => 'No valid contacts selected.']);
            }

            // Handle multiple image uploads and create collage
            $imagePath = null;
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $storedPath = $image->store('campaign-images', 'public');
                    // Normalize path for cross-platform compatibility
                    $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath));
                    $imagePaths[] = $fullPath;
                }

                // Create collage if multiple images
                if (count($imagePaths) > 1) {
                    $collageService = new ImageCollageService();
                    $imagePath = $collageService->createCollage($imagePaths);

                    // Cleanup original images after collage creation (saves storage)
                    foreach ($imagePaths as $originalPath) {
                        if (file_exists($originalPath)) {
                            @unlink($originalPath);
                        }
                    }
                } elseif (count($imagePaths) === 1) {
                    $imagePath = $imagePaths[0];
                }
            }

            // Get the user's WhatsApp session and token
            $userSession = $user->whatsapp_session;
            $userToken = $user->whatsapp_token;

            if (!$userSession) {
                return back()->withErrors(['session' => 'يرجى ربط حساب WhatsApp الخاص بك أولاً من إعدادات الحساب.']);
            }

            // ====== ASYNC SESSION MANAGEMENT ======
            // Session wake/validation is now handled in the job layer (SendWhatsappCampaign)
            // This allows instant HTTP response instead of 5-30s blocking
            // Only do a quick sanity check for known-disconnected users
            if ($user->session_state === 'disconnected') {
                return redirect()->route('login.reconnect')
                    ->with('warning', 'يرجى إعادة ربط حساب WhatsApp للاستمرار في الإرسال.');
            }

            // ====== SESSION WAKE & CONNECTION CHECK ======
            // For sleeping sessions, we need to wake them first before checking connection
            // This handles the case where session was closed after reconnect to save RAM
            $sessionManager = new \App\Services\SessionManager();

            if ($user->session_state === 'sleeping' || $user->session_state === 'none' || !$user->session_state) {
                // Wake the session first
                $wakeResult = $sessionManager->wakeSession($user);

                if ($wakeResult['status'] === 'needs_qr') {
                    // User logged out from mobile - needs to re-scan QR
                    $user->update(['session_state' => 'disconnected']);
                    $this->quotaService->releaseReservedQuota($user, $contactCount);

                    return redirect()->route('login.reconnect')
                        ->with('warning', 'تم فقدان اتصال WhatsApp من الموبايل. يرجى إعادة الربط.');
                }

                if ($wakeResult['status'] !== 'connected') {
                    // Session couldn't be woken - might be a server issue
                    $this->quotaService->releaseReservedQuota($user, $contactCount);

                    return back()->withErrors([
                        'session' => $wakeResult['message'] ?? 'خطأ في تشغيل جلسة WhatsApp. حاول مرة أخرى.'
                    ]);
                }

                // Session is now awake and connected
                $user->update(['session_state' => 'active']);
            } else {
                // Session should be active - do a quick live check
                $whatsapp = new \App\Services\WhatsAppService($userSession, $userToken);
                $connectionStatus = $whatsapp->checkConnection();

                if (!($connectionStatus['connected'] ?? false)) {
                    // Connection check failed - session might be sleeping (browser closed)
                    // Try waking the session before declaring it disconnected
                    \Illuminate\Support\Facades\Log::info("Quick live check failed for user {$user->id}, attempting wake...");

                    $wakeResult = $sessionManager->wakeSession($user);

                    if ($wakeResult['status'] === 'needs_qr') {
                        // User logged out from mobile - needs to re-scan QR
                        $user->update(['session_state' => 'disconnected']);
                        $this->quotaService->releaseReservedQuota($user, $contactCount);

                        return redirect()->route('login.reconnect')
                            ->with('warning', 'تم فقدان اتصال WhatsApp من الموبايل. يرجى إعادة الربط.');
                    }

                    if ($wakeResult['status'] !== 'connected') {
                        // Session couldn't be woken - might be a server issue
                        $this->quotaService->releaseReservedQuota($user, $contactCount);

                        return back()->withErrors([
                            'session' => $wakeResult['message'] ?? 'خطأ في تشغيل جلسة WhatsApp. حاول مرة أخرى.'
                        ]);
                    }

                    // Session is now awake and connected
                    $user->update(['session_state' => 'active']);
                }
            }

            // Pre-mark session as intended-active (job will do the real validation)
            $sessionManager->markSessionActive($user->id, $userSession);

            // Dispatch jobs with throttling (15 seconds delay per contact)
            $delay = 0;
            $totalContacts = $contacts->count();
            $currentIndex = 0;

            // SERIAL BATCH: Mark campaign as active BEFORE dispatching jobs
            // TTL = estimated completion time + 60 seconds buffer
            $estimatedDurationSeconds = ($totalContacts * 15) + 60;
            Cache::put("campaign_active:{$user->id}", true, now()->addSeconds($estimatedDurationSeconds));
            Cache::put("campaign_progress:{$user->id}", ['sent' => 0, 'total' => $totalContacts], now()->addSeconds($estimatedDurationSeconds));

            foreach ($contacts as $contact) {
                $currentIndex++;
                $isLastInBatch = ($currentIndex === $totalContacts);

                // Extract first name from contact name (first word)
                $firstName = explode(' ', trim($contact->name))[0] ?? $contact->name;

                // Replace placeholder with actual name
                $personalizedMessage = str_replace('{{ اسم_المستلم }}', $firstName, $validated['message']);

                SendWhatsappCampaign::dispatch(
                    $contact->phone,
                    $personalizedMessage,
                    $imagePath,
                    $userSession,
                    $userToken,
                    $user->id,           // Pass userId for session management
                    $isLastInBatch       // Flag to close session after last message
                )->delay(now()->addSeconds($delay));

                $delay += 15; // Add 15 seconds delay for each subsequent message
            }

            // NOTE: Quota was already reserved atomically at the start of send()
            // No need to record usage here - it was done during reservation
            // $this->quotaService->recordUsage($user, $contacts->count());

            $count = $contacts->count();
            $estimatedTime = ceil($delay / 60);

            // Get updated quota status for success message
            $newStatus = $this->quotaService->getQuotaStatus($user);

            return redirect()->route('campaigns.create')
                ->with('success', "تم جدولة الحملة بنجاح! سيتم الإرسال إلى {$count} مستلم. الوقت المقدر: {$estimatedTime} دقيقة.")
                ->with('quotaUpdate', $newStatus);
        } finally {
            // Always release the lock when done
            $lock->release();
        }
    }

    /**
     * Check WhatsApp connection status via AJAX.
     * Used for periodic connection monitoring in the UI.
     */
    public function whatsappStatus(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->whatsapp_session) {
            return response()->json([
                'connected' => false,
                'message' => 'لا توجد جلسة WhatsApp مرتبطة',
            ]);
        }

        // For sleeping sessions, report as connected without waking
        // The session will wake when user actually sends a campaign
        if ($user->session_state === 'sleeping') {
            return response()->json([
                'connected' => true,
                'status' => 'sleeping',
                'message' => 'متصل (الجلسة خاملة)',
            ]);
        }

        try {
            $whatsapp = new \App\Services\WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $status = $whatsapp->checkConnection();

            $connected = $status['connected'] ?? false;

            // Only mark as disconnected if session was supposed to be active
            // Don't overwrite sleeping or none states - they will be handled on campaign send
            if (!$connected && $user->session_state === 'active') {
                $user->update(['session_state' => 'disconnected']);
            }

            return response()->json([
                'connected' => $connected,
                'status' => $status['status'] ?? 'unknown',
                'message' => $connected ? 'متصل' : 'غير متصل',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'message' => 'خطأ في التحقق من الاتصال',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Force logout user when WhatsApp disconnection is detected.
     * Called via AJAX when the periodic check detects disconnection.
     */
    public function forceLogout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Log the event
            \Illuminate\Support\Facades\Log::info("Force logout due to WhatsApp disconnection for user {$user->id}");

            // Logout the user
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بسبب قطع اتصال WhatsApp',
            'redirect' => route('login'),
        ]);
    }

    /**
     * Get current campaign status via AJAX.
     * Used for polling to check if a campaign is active.
     */
    public function campaignStatus(Request $request)
    {
        $user = Auth::user();
        $isActive = Cache::has("campaign_active:{$user->id}");
        $progress = Cache::get("campaign_progress:{$user->id}", ['sent' => 0, 'total' => 0]);

        return response()->json([
            'active' => $isActive,
            'sent' => $progress['sent'],
            'total' => $progress['total'],
            'message' => $isActive ? 'جاري الإرسال...' : 'جاهز للإرسال',
        ]);
    }
}

