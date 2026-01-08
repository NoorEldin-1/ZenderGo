<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription status page.
     */
    public function index()
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription();
        $subscriptionPrice = SystemSetting::getSubscriptionPrice();
        $vodafoneCashNumber = SystemSetting::getVodafoneCashNumber();
        $instapayNumber = SystemSetting::getInstapayNumber();

        // Get payment requests for this user
        $pendingRequest = PaymentRequest::where('user_id', $user->id)
            ->pending()
            ->latest()
            ->first();

        $lastRejectedRequest = PaymentRequest::where('user_id', $user->id)
            ->rejected()
            ->latest()
            ->first();

        // Only show last rejected if no pending and it was recent (last 7 days)
        if ($pendingRequest || ($lastRejectedRequest && $lastRejectedRequest->reviewed_at->diffInDays(now()) > 7)) {
            $lastRejectedRequest = null;
        }

        $paymentHistory = PaymentRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->take(10)
            ->get();

        return view('subscription.index', compact(
            'subscription',
            'subscriptionPrice',
            'vodafoneCashNumber',
            'instapayNumber',
            'pendingRequest',
            'lastRejectedRequest',
            'paymentHistory'
        ));
    }

    /**
     * Submit a payment request with receipt image.
     */
    public function submitPayment(Request $request)
    {
        $user = auth()->user();

        // Check if user has an active trial - block subscription during trial
        $subscription = $user->activeSubscription();
        if ($subscription && $subscription->isActive() && $subscription->isTrial()) {
            return back()->withErrors(['receipt' => 'لا يمكنك الاشتراك أثناء الفترة التجريبية. انتظر حتى انتهاء الفترة التجريبية.']);
        }

        // Check if user already has a pending request
        $hasPending = PaymentRequest::where('user_id', $user->id)
            ->pending()
            ->exists();

        if ($hasPending) {
            return back()->withErrors(['receipt' => 'لديك طلب قيد المراجعة بالفعل. يرجى الانتظار.']);
        }

        $request->validate([
            'receipt' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // Max 5MB
        ], [
            'receipt.required' => 'يرجى رفع صورة الوصل.',
            'receipt.image' => 'الملف يجب أن يكون صورة.',
            'receipt.mimes' => 'الصورة يجب أن تكون بصيغة: JPG, PNG, أو WebP.',
            'receipt.max' => 'حجم الصورة يجب أن لا يتجاوز 5 ميجابايت.',
        ]);

        // Store the receipt image
        $path = $request->file('receipt')->store('payment-receipts', 'public');

        // Create payment request
        PaymentRequest::create([
            'user_id' => $user->id,
            'amount' => SystemSetting::getSubscriptionPrice(),
            'receipt_image' => $path,
            'status' => 'pending',
        ]);

        return back()->with('success', 'تم إرسال طلبك بنجاح! سيتم مراجعته وتفعيل اشتراكك في أقرب وقت.');
    }
}

