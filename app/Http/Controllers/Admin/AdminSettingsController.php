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
            'trial_duration' => SystemSetting::getTrialDuration(),
            'trial_duration_unit' => SystemSetting::getTrialDurationUnit(),
            'subscription_price' => SystemSetting::getSubscriptionPrice(),
            'vodafone_cash_number' => SystemSetting::getVodafoneCashNumber(),
            'instapay_number' => SystemSetting::getInstapayNumber(),
            'support_phone_number' => SystemSetting::getSupportPhoneNumber(),
            'contact_limit' => SystemSetting::getContactLimit(),
            'campaign_limit' => SystemSetting::getCampaignLimit(),
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'trial_duration' => 'required|integer|min:1|max:9999',
            'trial_duration_unit' => 'required|in:minutes,hours,days',
            'subscription_price' => 'required|numeric|min:0',
            'vodafone_cash_number' => 'required|string|regex:/^01[0-9]{9}$/',
            'instapay_number' => 'nullable|string|max:50',
            'support_phone_number' => 'required|string|regex:/^01[0-9]{9}$/',
            'contact_limit' => 'required|integer|min:10|max:100000',
            'campaign_limit' => 'required|integer|min:1|max:1000',
        ], [
            'vodafone_cash_number.regex' => 'رقم فودافون كاش غير صحيح. يجب أن يبدأ بـ 01 ويتكون من 11 رقم.',
            'instapay_number.max' => 'رقم إنستاباي طويل جداً.',
            'support_phone_number.regex' => 'رقم الدعم غير صحيح. يجب أن يبدأ بـ 01 ويتكون من 11 رقم.',
            'trial_duration_unit.in' => 'وحدة المدة غير صحيحة.',
            'contact_limit.min' => 'الحد الأدنى لجهات الاتصال هو 10.',
            'contact_limit.max' => 'الحد الأقصى لجهات الاتصال هو 100,000.',
        ]);

        SystemSetting::set('trial_duration', $request->trial_duration);
        SystemSetting::set('trial_duration_unit', $request->trial_duration_unit);
        SystemSetting::set('subscription_price', $request->subscription_price);
        SystemSetting::set('vodafone_cash_number', $request->vodafone_cash_number);
        SystemSetting::set('instapay_number', $request->instapay_number ?? '');
        SystemSetting::set('support_phone_number', $request->support_phone_number);
        SystemSetting::set('contact_limit', $request->contact_limit);
        SystemSetting::set('campaign_limit', $request->campaign_limit);

        return back()->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}


