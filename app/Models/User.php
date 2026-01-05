<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'whatsapp_session',
        'whatsapp_token',
        'is_suspended',
        'suspension_reason',
        'suspended_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Get the suspension reason in Arabic with support contact.
     */
    public function getSuspensionReasonTextAttribute(): string
    {
        $supportPhone = SystemSetting::getSupportPhoneNumber();
        return match ($this->suspension_reason) {
            'security' => "الحساب معطل لسبب أمني. للتواصل مع الدعم: {$supportPhone}",
            'subscription' => "يرجى دفع الاشتراك لتستطيع التكملة. للتواصل: {$supportPhone}",
            default => '',
        };
    }

    /**
     * Check if the user is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->is_suspended;
    }

    /**
     * Suspend the user account.
     */
    public function suspend(string $reason): void
    {
        $this->update([
            'is_suspended' => true,
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);
    }

    /**
     * Unsuspend the user account.
     */
    public function unsuspend(): void
    {
        $this->update([
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);
    }

    /**
     * Scope to get suspended users.
     */
    public function scopeSuspended($query)
    {
        return $query->where('is_suspended', true);
    }

    /**
     * Get the contacts for the user.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the templates for the user.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Get share requests sent by this user.
     */
    public function sentShareRequests(): HasMany
    {
        return $this->hasMany(ShareRequest::class, 'sender_id');
    }

    /**
     * Get share requests received by this user.
     */
    public function receivedShareRequests(): HasMany
    {
        return $this->hasMany(ShareRequest::class, 'recipient_id');
    }

    /**
     * Get count of pending share requests for this user.
     */
    public function getPendingShareRequestsCountAttribute(): int
    {
        return $this->receivedShareRequests()->pending()->count();
    }

    /**
     * Get template share requests sent by this user.
     */
    public function sentTemplateShareRequests(): HasMany
    {
        return $this->hasMany(TemplateShareRequest::class, 'sender_id');
    }

    /**
     * Get template share requests received by this user.
     */
    public function receivedTemplateShareRequests(): HasMany
    {
        return $this->hasMany(TemplateShareRequest::class, 'recipient_id');
    }

    /**
     * Get count of pending template share requests for this user.
     */
    public function getPendingTemplateShareRequestsCountAttribute(): int
    {
        return $this->receivedTemplateShareRequests()->pending()->count();
    }

    /**
     * Get the subscriptions for the user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the current active subscription.
     */
    public function activeSubscription()
    {
        return $this->subscriptions()->active()->latest('ends_at')->first();
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get subscription status: trial, paid, or expired.
     */
    public function getSubscriptionStatusAttribute(): string
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            return 'expired';
        }

        return $subscription->type;
    }

    /**
     * Get days remaining in current subscription.
     */
    public function getSubscriptionDaysRemainingAttribute(): int
    {
        $subscription = $this->activeSubscription();
        return $subscription ? $subscription->daysRemaining() : 0;
    }

    /**
     * Get the end date of current subscription.
     */
    public function getSubscriptionEndsAtAttribute()
    {
        $subscription = $this->activeSubscription();
        return $subscription ? $subscription->ends_at : null;
    }

    /**
     * Create a trial subscription for this user.
     */
    public function createTrialSubscription(): Subscription
    {
        $trialDays = SystemSetting::getTrialDays();

        return $this->subscriptions()->create([
            'type' => 'trial',
            'price_paid' => 0,
            'starts_at' => now(),
            'ends_at' => now()->addDays($trialDays),
        ]);
    }

    /**
     * Create or extend a paid subscription for this user.
     * If user has an active subscription, extends it instead of creating new.
     */
    public function createPaidSubscription(): Subscription
    {
        $price = SystemSetting::getSubscriptionPrice();

        // Check if there's an active subscription
        $activeSubscription = $this->activeSubscription();

        if ($activeSubscription) {
            // Extend the current subscription by one month from its end date
            $activeSubscription->update([
                'ends_at' => $activeSubscription->ends_at->addMonth(),
                'type' => 'paid',
                'price_paid' => $activeSubscription->price_paid + $price,
            ]);
            return $activeSubscription->fresh();
        }

        // Create new subscription if no active one exists
        return $this->subscriptions()->create([
            'type' => 'paid',
            'price_paid' => $price,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }
}
