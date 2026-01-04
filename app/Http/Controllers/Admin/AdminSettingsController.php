<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = [
            'trial_days' => SystemSetting::getTrialDays(),
            'subscription_price' => SystemSetting::getSubscriptionPrice(),
            'vodafone_cash_number' => SystemSetting::getVodafoneCashNumber(),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'trial_days' => 'required|integer|min:1|max:365',
            'subscription_price' => 'required|numeric|min:0',
            'vodafone_cash_number' => 'required|string|regex:/^01[0-9]{9}$/',
        ], [
            'vodafone_cash_number.regex' => 'رقم فودافون كاش غير صحيح. يجب أن يبدأ بـ 01 ويتكون من 11 رقم.',
        ]);

        SystemSetting::set('trial_days', $request->trial_days);
        SystemSetting::set('subscription_price', $request->subscription_price);
        SystemSetting::set('vodafone_cash_number', $request->vodafone_cash_number);

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}

