<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect home to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest routes (login/verify)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'sendOtp']);
    Route::get('/verify', [AuthController::class, 'showVerifyForm'])->name('verify');
    Route::post('/verify', [AuthController::class, 'verifyOtp']);
});

// Protected routes (authenticated users only)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/whatsapp', [SettingsController::class, 'updateWhatsapp'])->name('settings.whatsapp');

    // WhatsApp Session Management (AJAX)
    Route::post('/settings/whatsapp/start', [SettingsController::class, 'startSession'])->name('settings.whatsapp.start');
    Route::get('/settings/whatsapp/qrcode', [SettingsController::class, 'getQrCode'])->name('settings.whatsapp.qrcode');
    Route::get('/settings/whatsapp/status', [SettingsController::class, 'checkConnection'])->name('settings.whatsapp.status');
    Route::post('/settings/whatsapp/disconnect', [SettingsController::class, 'disconnect'])->name('settings.whatsapp.disconnect');
});

