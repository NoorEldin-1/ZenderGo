<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;

class AdminPaymentRequestsController extends Controller
{
    /**
     * Display a listing of payment requests.
     */
    public function index(Request $request)
    {
        $query = PaymentRequest::with('user')->latest();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Search by user name or phone
        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $paymentRequests = $query->paginate(15);

        // Stats for widgets
        $pendingCount = PaymentRequest::pending()->count();
        $approvedCount = PaymentRequest::where('status', 'approved')->count();
        $rejectedCount = PaymentRequest::where('status', 'rejected')->count();
        $totalCount = PaymentRequest::count();

        return view('admin.payment-requests.index', compact(
            'paymentRequests',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'totalCount'
        ));
    }

    /**
     * Display the specified payment request.
     */
    public function show(PaymentRequest $paymentRequest)
    {
        $paymentRequest->load('user', 'reviewer');

        return view('admin.payment-requests.show', compact('paymentRequest'));
    }

    /**
     * Approve the payment request and activate subscription.
     */
    public function approve(Request $request, PaymentRequest $paymentRequest)
    {
        if (!$paymentRequest->isPending()) {
            return back()->withErrors(['error' => 'هذا الطلب تم معالجته بالفعل.']);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $paymentRequest->user;

        // Update payment request
        $paymentRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->notes,
            'reviewed_at' => now(),
            'reviewed_by' => auth('admin')->id(),
        ]);

        // Delete the receipt image to save storage
        $paymentRequest->deleteReceiptImage();

        // Unsuspend user if suspended for subscription
        if ($user->is_suspended && $user->suspension_reason === 'subscription') {
            $user->unsuspend();
        }

        // Create paid subscription
        $user->createPaidSubscription();

        return redirect()->route('admin.payment-requests.index')
            ->with('success', "تم قبول الطلب وتفعيل اشتراك {$user->name} بنجاح.");
    }

    /**
     * Reject the payment request.
     */
    public function reject(Request $request, PaymentRequest $paymentRequest)
    {
        if (!$paymentRequest->isPending()) {
            return back()->withErrors(['error' => 'هذا الطلب تم معالجته بالفعل.']);
        }

        $request->validate([
            'notes' => 'required|string|max:500',
        ], [
            'notes.required' => 'يرجى كتابة سبب الرفض.',
        ]);

        // Update payment request
        $paymentRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->notes,
            'reviewed_at' => now(),
            'reviewed_by' => auth('admin')->id(),
        ]);

        // Delete the receipt image to save storage
        $paymentRequest->deleteReceiptImage();

        return redirect()->route('admin.payment-requests.index')
            ->with('success', 'تم رفض الطلب.');
    }
}
