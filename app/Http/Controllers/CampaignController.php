<?php

namespace App\Http\Controllers;

use App\Jobs\SendWhatsappCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
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

            $contacts = $query->latest()->paginate(20);

            return response()->json($contacts);
        }

        return view('campaigns.create');
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
            'image' => 'nullable|image|max:5120', // Max 5MB
        ], [
            'contacts.max' => 'عفواً، لا يمكن إرسال الحملة لأكثر من 50 مستلم في المرة الواحدة.'
        ]);

        // Get selected contacts that belong to the user
        $contacts = Auth::user()->contacts()
            ->whereIn('id', $validated['contacts'])
            ->get();

        if ($contacts->isEmpty()) {
            return back()->withErrors(['contacts' => 'No valid contacts selected.']);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('campaign-images', 'public');
            $imagePath = storage_path('app/public/' . $imagePath);
        }

        // Get the user's WhatsApp session and token
        $user = Auth::user();
        $userSession = $user->whatsapp_session;
        $userToken = $user->whatsapp_token;

        if (!$userSession) {
            return back()->withErrors(['session' => 'يرجى ربط حساب WhatsApp الخاص بك أولاً من إعدادات الحساب.']);
        }

        // Dispatch jobs with throttling (15 seconds delay per contact)
        $delay = 0;
        foreach ($contacts as $contact) {
            SendWhatsappCampaign::dispatch(
                $contact->phone,
                $validated['message'],
                $imagePath,
                $userSession,
                $userToken
            )->delay(now()->addSeconds($delay));

            $delay += 15; // Add 15 seconds delay for each subsequent message
        }

        $count = $contacts->count();
        $estimatedTime = ceil($delay / 60);

        return redirect()->route('campaigns.create')
            ->with('success', "تم جدولة الحملة بنجاح! سيتم الإرسال إلى {$count} مستلم. الوقت المقدر: {$estimatedTime} دقيقة.");
    }
}
