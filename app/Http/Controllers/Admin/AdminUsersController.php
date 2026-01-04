<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount(['contacts', 'templates'])
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->loadCount(['contacts', 'templates', 'sentShareRequests', 'receivedShareRequests']);
        $user->load([
            'contacts' => function ($query) {
                $query->latest()->take(10);
            }
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Suspend the specified user.
     */
    public function suspend(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|in:security,subscription'
        ]);

        $user->suspend($request->reason);

        $reasonText = $request->reason === 'security' ? 'أمني' : 'اشتراك';

        return back()->with('success', "تم تعطيل الحساب بنجاح (السبب: {$reasonText}).");
    }

    /**
     * Unsuspend the specified user.
     */
    public function unsuspend(User $user)
    {
        $user->unsuspend();

        return back()->with('success', 'تم إعادة تفعيل الحساب بنجاح.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح.');
    }

    /**
     * Activate a paid subscription for the specified user.
     */
    public function activateSubscription(User $user)
    {
        // Unsuspend user if they were suspended for subscription
        if ($user->is_suspended && $user->suspension_reason === 'subscription') {
            $user->unsuspend();
        }

        // Create paid subscription (30 days)
        $subscription = $user->createPaidSubscription();

        $price = $subscription->price_paid;
        $endsAt = $subscription->ends_at->format('Y/m/d');

        return back()->with('success', "تم تفعيل الاشتراك الشهري ({$price} جنيه) حتى {$endsAt}.");
    }
}
