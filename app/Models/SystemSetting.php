<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Get trial duration value.
     */
    public static function getTrialDuration(): int
    {
        return (int) static::get('trial_duration', 7);
    }

    /**
     * Get trial duration unit (minutes, hours, days).
     */
    public static function getTrialDurationUnit(): string
    {
        return (string) static::get('trial_duration_unit', 'days');
    }

    /**
     * Get trial duration in minutes for calculations.
     */
    public static function getTrialDurationInMinutes(): int
    {
        $value = static::getTrialDuration();
        $unit = static::getTrialDurationUnit();

        return match ($unit) {
            'minutes' => $value,
            'hours' => $value * 60,
            'days' => $value * 60 * 24,
            default => $value * 60 * 24, // default to days
        };
    }

    /**
     * Get subscription price setting.
     */
    public static function getSubscriptionPrice(): float
    {
        return (float) static::get('subscription_price', 100);
    }

    /**
     * Get Vodafone Cash number setting.
     */
    public static function getVodafoneCashNumber(): string
    {
        return (string) static::get('vodafone_cash_number', '01XXXXXXXXX');
    }

    /**
     * Get InstaPay number/handle setting.
     */
    public static function getInstapayNumber(): string
    {
        return (string) static::get('instapay_number', '');
    }

    /**
     * Get Support phone number setting.
     */
    public static function getSupportPhoneNumber(): string
    {
        return (string) static::get('support_phone_number', '01000000000');
    }

    /**
     * Get campaign quota limit (max contacts per window).
     */
    public static function getCampaignQuotaLimit(): int
    {
        return (int) static::get('campaign_quota_limit', 100);
    }

    /**
     * Get campaign quota window in hours.
     */
    public static function getCampaignQuotaWindowHours(): int
    {
        return (int) static::get('campaign_quota_window_hours', 5);
    }

    /**
     * Get contact limit per user.
     */
    public static function getContactLimit(): int
    {
        return (int) static::get('contact_limit', 1000);
    }

    /**
     * Get campaign send limit per batch.
     */
    public static function getCampaignLimit(): int
    {
        return (int) static::get('campaign_limit', 10);
    }
}
