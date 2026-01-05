<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateShareController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint for monitoring (no auth required)
Route::get('/health', [HealthController::class, 'check'])->name('health.check');

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
        Route::delete('/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->middleware('rate.heavy:bulk_delete')->name('contacts.bulk-delete');
        Route::post('/contacts/preview', [ContactController::class, 'previewImport'])->middleware('rate.heavy:import')->name('contacts.preview');
        Route::post('/contacts/confirm-import', [ContactController::class, 'confirmImport'])->middleware('rate.heavy:import')->name('contacts.confirm-import');
        Route::resource('contacts', ContactController::class)->except(['show', 'edit']);

        // Campaigns
        Route::get('/campaigns', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('/campaigns/send', [CampaignController::class, 'send'])->middleware('rate.heavy:campaign')->name('campaigns.send');



        // Templates API
        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

        // Template Sharing (Ephemeral - no history kept)
        Route::get('/templates/share/pending', [TemplateShareController::class, 'pending'])->name('templates.share.pending');
        Route::post('/templates/share', [TemplateShareController::class, 'store'])->name('templates.share.store');
        Route::post('/templates/share/{id}/accept', [TemplateShareController::class, 'accept'])->name('templates.share.accept');
        Route::post('/templates/share/{id}/reject', [TemplateShareController::class, 'reject'])->name('templates.share.reject');
        Route::delete('/templates/share/{id}', [TemplateShareController::class, 'destroy'])->name('templates.share.destroy');

        // Share Requests
        Route::get('/shares', [ShareController::class, 'index'])->name('shares.index');
        Route::post('/shares', [ShareController::class, 'store'])->middleware('rate.heavy:share')->name('shares.store');
        Route::post('/shares/{id}/accept', [ShareController::class, 'accept'])->name('shares.accept');
        Route::post('/shares/{id}/reject', [ShareController::class, 'reject'])->name('shares.reject');
        Route::delete('/shares/{id}', [ShareController::class, 'destroy'])->name('shares.destroy');
    });
});
