<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateShareRequest;
use App\Models\TemplateShareItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemplateShareController extends Controller
{
    /**
     * Get pending template share requests for current user.
     */
    public function pending()
    {
        $requests = Auth::user()
            ->receivedTemplateShareRequests()
            ->pending()
            ->with(['sender', 'items'])
            ->latest()
            ->get();

        return response()->json($requests);
    }

    /**
     * Store a new template share request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'templates' => 'required|array|min:1',
            'templates.*' => 'exists:templates,id',
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'templates.required' => 'يجب اختيار قالب واحد على الأقل',
            'templates.min' => 'يجب اختيار قالب واحد على الأقل',
        ]);

        // Find recipient by phone
        $recipient = User::where('phone', $validated['phone'])->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد مستخدم بهذا الرقم'
            ], 404);
        }

        // Prevent sharing with self
        if ($recipient->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك مشاركة القوالب مع نفسك'
            ], 400);
        }

        // Validate templates belong to sender
        $templates = Auth::user()->templates()
            ->whereIn('id', $validated['templates'])
            ->get();

        if ($templates->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد قوالب صالحة'
            ], 400);
        }

        DB::transaction(function () use ($recipient, $templates) {
            // Create share request
            $shareRequest = TemplateShareRequest::create([
                'sender_id' => Auth::id(),
                'recipient_id' => $recipient->id,
                'status' => 'pending',
            ]);

            // Store template snapshots
            foreach ($templates as $template) {
                TemplateShareItem::create([
                    'template_share_request_id' => $shareRequest->id,
                    'name' => $template->name,
                    'content' => $template->content,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب المشاركة بنجاح!'
        ]);
    }

    /**
     * Accept a template share request.
     * Copies templates to recipient then DELETES the request (ephemeral).
     */
    public function accept($id)
    {
        $shareRequest = TemplateShareRequest::where('recipient_id', Auth::id())
            ->where('status', 'pending')
            ->with('items')
            ->findOrFail($id);

        $count = 0;

        DB::transaction(function () use ($shareRequest, &$count) {
            // Copy templates to recipient
            $now = now();
            $userId = Auth::id();
            $newTemplates = [];

            foreach ($shareRequest->items as $item) {
                $newTemplates[] = [
                    'user_id' => $userId,
                    'name' => $item->name,
                    'content' => $item->content,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($newTemplates)) {
                Template::insert($newTemplates);
                $count = count($newTemplates);
            }

            // DELETE the entire request and related items (cascade)
            $shareRequest->delete();
        });

        return response()->json([
            'success' => true,
            'message' => "تم قبول الطلب! تمت إضافة {$count} قالب."
        ]);
    }

    /**
     * Reject a template share request.
     * Simply DELETES the request (ephemeral).
     */
    public function reject($id)
    {
        $shareRequest = TemplateShareRequest::where('recipient_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        // DELETE the entire request and related items (cascade)
        $shareRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم رفض طلب المشاركة.'
        ]);
    }

    /**
     * Cancel a pending share request (by sender).
     */
    public function destroy($id)
    {
        $shareRequest = TemplateShareRequest::where('sender_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $shareRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء طلب المشاركة.'
        ]);
    }
}
