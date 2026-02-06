<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ShareRequest;
use App\Models\ShareRequestContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShareController extends Controller
{
    /**
     * Display all share requests (sent and received).
     */
    public function index()
    {
        $user = Auth::user();

        $receivedRequests = $user->receivedShareRequests()
            ->with(['sender', 'contacts'])
            ->latest()
            ->get();

        $sentRequests = $user->sentShareRequests()
            ->with(['recipient', 'contacts'])
            ->latest()
            ->get();

        $pendingCount = $receivedRequests->where('status', 'pending')->count();

        return view('shares.index', compact('receivedRequests', 'sentRequests', 'pendingCount'));
    }

    /**
     * Store a new share request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'contacts' => 'required|array|min:1',
            'contacts.*' => 'exists:contacts,id',
            'message' => 'nullable|string|max:500',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'contacts.required' => 'يجب اختيار جهة اتصال واحدة على الأقل',
            'contacts.min' => 'يجب اختيار جهة اتصال واحدة على الأقل',
        ]);

        // Find recipient by phone
        $recipient = User::where('phone', $validated['phone'])->first();

        if (!$recipient) {
            return back()->withErrors(['phone' => 'لا يوجد مستخدم بهذا الرقم'])->withInput();
        }

        // Prevent sharing with self
        if ($recipient->id === Auth::id()) {
            return back()->withErrors(['phone' => 'لا يمكنك مشاركة جهات الاتصال مع نفسك'])->withInput();
        }

        // Validate contacts belong to sender
        $contacts = Auth::user()->contacts()
            ->whereIn('id', $validated['contacts'])
            ->get();

        if ($contacts->isEmpty()) {
            return back()->withErrors(['contacts' => 'لا توجد جهات اتصال صالحة'])->withInput();
        }

        DB::transaction(function () use ($validated, $recipient, $contacts) {
            // Create share request
            $shareRequest = ShareRequest::create([
                'sender_id' => Auth::id(),
                'recipient_id' => $recipient->id,
                'status' => 'pending',
                'message' => $validated['message'] ?? null,
            ]);

            // Store contact snapshots with original ID
            foreach ($contacts as $contact) {
                ShareRequestContact::create([
                    'share_request_id' => $shareRequest->id,
                    'contact_id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                    'store_name' => $contact->store_name,
                ]);
            }
        });

        return back()->with('success', 'تم إرسال طلب المشاركة بنجاح! سيتم إشعار المستلم.');
    }

    /**
     * Accept a share request.
     * Optimized to avoid N+1 queries by batch loading existing phones.
     */
    public function accept($id)
    {
        $shareRequest = ShareRequest::where('recipient_id', Auth::id())
            ->where('status', 'pending')
            ->with('contacts')
            ->findOrFail($id);

        DB::transaction(function () use ($shareRequest) {
            // Update request status
            $shareRequest->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            // Batch load all existing phones for this user (avoid N+1)
            $existingPhones = Auth::user()->contacts()
                ->pluck('phone')
                ->flip()
                ->toArray();

            // Prepare batch insert data
            $newContacts = [];
            $now = now();
            $userId = Auth::id();

            foreach ($shareRequest->contacts as $contactData) {
                // Check in memory instead of querying DB
                if (!isset($existingPhones[$contactData->phone])) {
                    $newContacts[] = [
                        'user_id' => $userId,
                        'name' => $contactData->name,
                        'phone' => $contactData->phone,
                        'store_name' => $contactData->store_name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    // Mark as existing to avoid duplicates within this batch
                    $existingPhones[$contactData->phone] = true;
                }
            }

            // Single batch insert instead of multiple individual inserts
            if (!empty($newContacts)) {
                Contact::insert($newContacts);
            }
        });

        $count = $shareRequest->contacts->count();
        return back()->with('success', "تم قبول الطلب! تمت إضافة {$count} جهة اتصال.");
    }

    /**
     * Reject a share request.
     */
    public function reject($id)
    {
        $shareRequest = ShareRequest::where('recipient_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $shareRequest->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);

        return back()->with('success', 'تم رفض طلب المشاركة.');
    }

    /**
     * Cancel a pending share request (by sender).
     */
    public function destroy($id)
    {
        $shareRequest = ShareRequest::where('sender_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $shareRequest->delete();

        return back()->with('success', 'تم إلغاء طلب المشاركة.');
    }
}
