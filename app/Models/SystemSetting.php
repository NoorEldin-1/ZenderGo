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
     * Get trial days setting.
     */
    public static function getTrialDays(): int
    {
        return (int) static::get('trial_days', 7);
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
     * Get Support phone number setting.
     */
    public static function getSupportPhoneNumber(): string
    {
        return (string) static::get('support_phone_number', '01000000000');
    }
}
