<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect home to login or guide
Route::get('/', function () {
    return auth()->check() ? redirect()->route('guide') : redirect()->route('login');
});

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Reconnect (for existing users with expired sessions)
    Route::get('/login/reconnect', [AuthController::class, 'showReconnectForm'])->name('login.reconnect');
    Route::post('/login/reconnect/start', [AuthController::class, 'startReconnect'])->name('login.reconnect.start');
    Route::get('/login/reconnect/check', [AuthController::class, 'checkReconnect'])->name('login.reconnect.check');

    // Registration
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register/start', [AuthController::class, 'startRegistration'])->name('register.start');
    Route::get('/register/check', [AuthController::class, 'checkRegistration'])->name('register.check');
});

// Protected routes (authenticated users only)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Guide
    Route::get('/guide', function () {
        return view('guide');
    })->name('guide');

    // Subscription Locked Page
    Route::view('/subscription/locked', 'errors.subscription_locked')->name('subscription.locked');

    // Subscription Routes (Accessible even if suspended for subscription)
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/payment', [SubscriptionController::class, 'submitPayment'])->name('subscription.payment');

    // Protected by Subscription Status
    Route::middleware('subscription.active')->group(function () {
        // Contacts - Custom routes BEFORE resource
        Route::delete('/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');
        Route::post('/contacts/preview', [ContactController::class, 'previewImport'])->name('contacts.preview');
        Route::post('/contacts/confirm-import', [ContactController::class, 'confirmImport'])->name('contacts.confirm-import');
        Route::resource('contacts', ContactController::class)->except(['show', 'edit']);

        // Campaigns
        Route::get('/campaigns', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns/send', [CampaignController::class, 'send'])->name('campaigns.send');

        // Templates API
        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

        // Share Requests
        Route::get('/shares', [ShareController::class, 'index'])->name('shares.index');
        Route::post('/shares', [ShareController::class, 'store'])->name('shares.store');
        Route::post('/shares/{id}/accept', [ShareController::class, 'accept'])->name('shares.accept');
        Route::post('/shares/{id}/reject', [ShareController::class, 'reject'])->name('shares.reject');
        Route::delete('/shares/{id}', [ShareController::class, 'destroy'])->name('shares.destroy');
    });
});
