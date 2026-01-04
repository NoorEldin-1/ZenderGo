<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPaymentRequestsController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for the admin panel, accessible at /admin/*
|
*/

// Guest routes (admin login)
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
});

// Protected admin routes
Route::middleware('admin.auth')->group(function () {
    // Dashboard
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Logout
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Users Management
    Route::resource('users', AdminUsersController::class)
        ->only(['index', 'show', 'destroy'])
        ->names([
            'index' => 'admin.users.index',
            'show' => 'admin.users.show',
            'destroy' => 'admin.users.destroy',
        ]);

    // User Suspension
    Route::post('/users/{user}/suspend', [AdminUsersController::class, 'suspend'])->name('admin.users.suspend');
    Route::post('/users/{user}/unsuspend', [AdminUsersController::class, 'unsuspend'])->name('admin.users.unsuspend');

    // User Subscription Activation
    Route::post('/users/{user}/activate-subscription', [AdminUsersController::class, 'activateSubscription'])->name('admin.users.activate-subscription');

    // Payment Requests
    Route::get('/payment-requests', [AdminPaymentRequestsController::class, 'index'])->name('admin.payment-requests.index');
    Route::get('/payment-requests/{paymentRequest}', [AdminPaymentRequestsController::class, 'show'])->name('admin.payment-requests.show');
    Route::post('/payment-requests/{paymentRequest}/approve', [AdminPaymentRequestsController::class, 'approve'])->name('admin.payment-requests.approve');
    Route::post('/payment-requests/{paymentRequest}/reject', [AdminPaymentRequestsController::class, 'reject'])->name('admin.payment-requests.reject');

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('admin.settings.update');
});

