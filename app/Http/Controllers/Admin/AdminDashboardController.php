<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\PaymentRequest;
use App\Models\Template;
use App\Models\User;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin dashboard with statistics.
     */
    public function index()
    {
        $stats = [
            'users_count' => User::count(),
            'contacts_count' => Contact::count(),
            'templates_count' => Template::count(),
            'pending_payments_count' => PaymentRequest::pending()->count(),
            'recent_users' => User::latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

