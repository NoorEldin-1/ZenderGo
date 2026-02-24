<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappCampaign;
use App\Models\Campaign;
use App\Services\CampaignQuotaService;
use App\Services\ImageCollageService;
use App\Models\SystemSetting;
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

            $limit = SystemSetting::getCampaignLimit();
            $contacts = $query->latest()->paginate($limit);

            return response()->json($contacts);
        }

        // Get quota status and campaign limit for display
        $quotaStatus = $this->quotaService->getQuotaStatus(Auth::user());
        $campaignLimit = SystemSetting::getCampaignLimit();

        return view('campaigns.create', compact('quotaStatus', 'campaignLimit'));
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

        // BLOCKER RULE: Check if user has an active campaign (DB-backed)
        $activeCampaign = Campaign::getActiveForUser($user->id);
        if ($activeCampaign) {
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
            $limit = SystemSetting::getCampaignLimit();

            // Base validation rules
            $rules = [
                'contacts' => 'required|array|min:1|max:' . $limit,
                'contacts.*' => 'exists:contacts,id',
                'message' => 'required|string|max:4096',
                'attachment_type' => 'nullable|in:none,image,document,video',
            ];

            $messages = [
                'contacts.max' => "عفواً، لا يمكن إرسال الحملة لأكثر من {$limit} مستلم في المرة الواحدة.",
            ];

            // Conditionally add attachment validation rules based on type
            $attachmentType = $request->input('attachment_type', 'none');

            if ($attachmentType === 'image') {
                $rules['images'] = 'required|array|min:1|max:5';
                $rules['images.*'] = 'image|max:5120';
                $messages['images.max'] = 'عفواً، الحد الأقصى هو 5 صور فقط.';
                $messages['images.*.max'] = 'عفواً، حجم كل صورة يجب ألا يتجاوز 5MB.';
            } elseif ($attachmentType === 'document') {
                $rules['document'] = 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv|max:10240';
                $messages['document.mimes'] = 'عفواً، صيغة الملف غير مدعومة. الصيغ المسموحة: PDF، Word، Excel، CSV.';
                $messages['document.max'] = 'عفواً، حجم الملف يجب ألا يتجاوز 10MB.';
            } elseif ($attachmentType === 'video') {
                $rules['video'] = 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo|max:16384';
                $messages['video.mimetypes'] = 'عفواً، صيغة الفيديو غير مدعومة. الصيغ المسموحة: MP4، MPEG، MOV، AVI.';
                $messages['video.max'] = 'عفواً، حجم الفيديو يجب ألا يتجاوز 16MB.';
            }

            $validated = $request->validate($rules, $messages);

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

            // Handle attachment uploads based on type
            $attachmentPath = null;

            if ($attachmentType === 'image' && $request->hasFile('images')) {
                // Existing image handling with collage support
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $storedPath = $image->store('campaign-images', 'public');
                    $fullPath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath));
                    $imagePaths[] = $fullPath;
                }

                if (count($imagePaths) > 1) {
                    $collageService = new ImageCollageService();
                    $attachmentPath = $collageService->createCollage($imagePaths);

                    foreach ($imagePaths as $originalPath) {
                        if (file_exists($originalPath)) {
                            @unlink($originalPath);
                        }
                    }
                } elseif (count($imagePaths) === 1) {
                    $attachmentPath = $imagePaths[0];
                }
            } elseif ($attachmentType === 'document' && $request->hasFile('document')) {
                $storedPath = $request->file('document')->store('campaign-documents', 'public');
                $attachmentPath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath));
            } elseif ($attachmentType === 'video' && $request->hasFile('video')) {
                $storedPath = $request->file('video')->store('campaign-videos', 'public');
                $attachmentPath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath));
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

            // Dispatch jobs immediately - sequential processing handled by blocking locks in the job
            $totalContacts = $contacts->count();
            $currentIndex = 0;

            // ====== DB-BACKED CAMPAIGN TRACKING ======
            // Create a Campaign record as the single source of truth.
            // This replaces the old Cache-based tracking which could get stuck.
            $campaign = Campaign::create([
                'user_id' => $user->id,
                'total_contacts' => $totalContacts,
                'status' => Campaign::STATUS_PROCESSING,
            ]);

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
                    $attachmentPath,
                    $userSession,
                    $userToken,
                    $user->id,
                    $isLastInBatch,
                    $campaign->id        // Pass campaign ID for DB-backed tracking
                ); // No delay - jobs process sequentially via blocking lock
            }

            // NOTE: Quota was already reserved atomically at the start of send()
            // No need to record usage here - it was done during reservation
            // $this->quotaService->recordUsage($user, $contacts->count());

            $count = $contacts->count();

            // Increment total messages sent for the user
            $user->increment('total_messages_sent', $count);

            // Get updated quota status for success message
            $newStatus = $this->quotaService->getQuotaStatus($user);

            return redirect()->route('campaigns.create')
                ->with('success', "تم جدولة الحملة بنجاح!")
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
     * Uses DB-backed Campaign model instead of Cache for reliability.
     */
    public function campaignStatus(Request $request)
    {
        $user = Auth::user();

        // Find the latest campaign for this user
        $campaign = Campaign::getLatestForUser($user->id);

        if (!$campaign) {
            return response()->json([
                'active' => false,
                'sent' => 0,
                'total' => 0,
                'status' => 'idle',
                'message' => 'جاهز للإرسال',
                'failure_reason' => null,
            ]);
        }

        $isActive = $campaign->isActive();

        // Build user-friendly status message
        $message = match ($campaign->status) {
            Campaign::STATUS_PROCESSING => 'جاري الإرسال...',
            Campaign::STATUS_PENDING => 'في انتظار البدء...',
            Campaign::STATUS_COMPLETED => 'تم الإرسال بنجاح',
            Campaign::STATUS_FAILED => 'فشل الإرسال',
            Campaign::STATUS_CANCELLED => 'تم إلغاء الحملة',
            default => 'جاهز للإرسال',
        };

        return response()->json([
            'active' => $isActive,
            'sent' => $campaign->sent_count,
            'total' => $campaign->total_contacts,
            'status' => $campaign->status,
            'message' => $message,
            'failure_reason' => $campaign->failure_reason,
        ]);
    }
}

