<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappCampaign;
use App\Services\CampaignQuotaService;
use App\Services\ImageCollageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($request->ajax()) {
            $query = Auth::user()->contacts();

            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $contacts = $query->latest()->paginate(50);

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
        $validated = $request->validate([
            'contacts' => 'required|array|min:1|max:50',
            'contacts.*' => 'exists:contacts,id',
            'message' => 'required|string|max:4096',
            'images' => 'nullable|array|max:5', // Max 5 images
            'images.*' => 'image|max:5120', // Max 5MB each
        ], [
            'contacts.max' => 'عفواً، لا يمكن إرسال الحملة لأكثر من 50 مستلم في المرة الواحدة.',
            'images.max' => 'عفواً، الحد الأقصى هو 5 صور فقط.',
            'images.*.max' => 'عفواً، حجم كل صورة يجب ألا يتجاوز 5MB.',
        ]);

        $user = Auth::user();
        $contactCount = count($validated['contacts']);

        // Check quota BEFORE processing
        if (!$this->quotaService->canSend($user, $contactCount)) {
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

        // Note: Session waking and disconnect detection is handled by the Job
        // This keeps the form submission fast and avoids response issues

        // Dispatch jobs with throttling (15 seconds delay per contact)
        $delay = 0;
        $totalContacts = $contacts->count();
        $currentIndex = 0;

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

        // Record usage AFTER successful dispatch
        $this->quotaService->recordUsage($user, $contacts->count());

        $count = $contacts->count();
        $estimatedTime = ceil($delay / 60);

        // Get updated quota status for success message
        $newStatus = $this->quotaService->getQuotaStatus($user);

        return redirect()->route('campaigns.create')
            ->with('success', "تم جدولة الحملة بنجاح! سيتم الإرسال إلى {$count} مستلم. الوقت المقدر: {$estimatedTime} دقيقة.")
            ->with('quotaUpdate', $newStatus);
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

        try {
            $whatsapp = new \App\Services\WhatsAppService($user->whatsapp_session, $user->whatsapp_token);
            $status = $whatsapp->checkConnection();

            $connected = $status['connected'] ?? false;

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
}

