<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateShareController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint for monitoring (no auth required)
Route::get('/health', [HealthController::class, 'check'])->name('health.check');

// Home route - Landing page for guests, contacts for authenticated users
Route::get('/', function () {
    return auth()->check() ? redirect()->route('contacts.index') : view('welcome');
})->name('home');

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Registration
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register/start', [AuthController::class, 'startRegistration'])->name('register.start');
    Route::get('/register/check', [AuthController::class, 'checkRegistration'])->name('register.check');

    // Forgot Password
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.send-otp');
    Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
    Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset-form');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.update-forgot');
});

// Reconnect routes (accessible to both guests and authenticated users)
// Guests: redirected from login when session expired
// Authenticated: redirected from middleware when WhatsApp disconnected
Route::get('/login/reconnect', [AuthController::class, 'showReconnectForm'])->name('login.reconnect');
Route::post('/login/reconnect/start', [AuthController::class, 'startReconnect'])->name('login.reconnect.start');
Route::get('/login/reconnect/check', [AuthController::class, 'checkReconnect'])->name('login.reconnect.check');


// Protected routes (authenticated users only)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Theme Toggle (AJAX)
    Route::post('/theme/toggle', [ThemeController::class, 'toggle'])->name('theme.toggle');

    // Subscription Locked Page
    Route::view('/subscription/locked', 'errors.subscription_locked')->name('subscription.locked');

    // Subscription Routes (Accessible even if suspended for subscription)
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/payment', [SubscriptionController::class, 'submitPayment'])->name('subscription.payment');

    // Password Change Routes (Accessible even if subscription is inactive)
    Route::get('/password/change', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.update');

    // Protected by Subscription Status
    Route::middleware(['subscription.active'])->group(function () {
        // Contacts - Custom routes BEFORE resource
        Route::delete('/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->middleware('rate.heavy:bulk_delete')->name('contacts.bulk-delete');
        Route::get('/contacts/preview', [ContactController::class, 'showPreview'])->name('contacts.preview.get');
        Route::post('/contacts/preview', [ContactController::class, 'previewImport'])->middleware('rate.heavy:import')->name('contacts.preview');
        Route::post('/contacts/confirm-import', [ContactController::class, 'confirmImport'])->middleware('rate.heavy:import')->name('contacts.confirm-import');
        Route::post('/contacts/process-mapping', [ContactController::class, 'processMapping'])->name('contacts.process-mapping');
        Route::get('/contacts/remap', [ContactController::class, 'remap'])->name('contacts.remap');
        Route::post('/contacts/{contact}/toggle-featured', [ContactController::class, 'toggleFeatured'])->name('contacts.toggle-featured');
        Route::patch('/contacts/{contact}/label', [ContactController::class, 'updateLabel'])->name('contacts.update-label');
        Route::patch('/contacts/{contact}/note', [ContactController::class, 'updateNote'])->name('contacts.update-note');
        Route::resource('contacts', ContactController::class)->except(['show', 'edit']);

        // Campaigns (quota system handles rate limiting based on contacts sent)
        Route::get('/campaigns', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::get('/campaigns/quota-status', [CampaignController::class, 'quotaStatus'])->name('campaigns.quota');
        Route::post('/campaigns/send', [CampaignController::class, 'send'])->name('campaigns.send');
        Route::get('/campaigns/status', [CampaignController::class, 'campaignStatus'])->name('campaigns.status');
        Route::get('/campaigns/send', fn() => redirect()->route('campaigns.create')); // Redirect GET to form
        Route::get('/whatsapp/status', [CampaignController::class, 'whatsappStatus'])->name('whatsapp.status');
        Route::post('/whatsapp/force-logout', [CampaignController::class, 'forceLogout'])->name('whatsapp.force-logout');



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
