<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Enums\SubscriptionStatus;
use Webkul\Security\Models\User;
use Webkul\Support\Traits\BelongsToCompany;

class Subscription extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'nursery_subscriptions';

    protected $fillable = [
        'child_id',
        'pricing_plan_id',
        'renewal_of_id',
        'start_date',
        'end_date',
        'base_price',
        'sibling_discount_pct',
        'discount_amount',
        'includes_tshirt',
        'tshirt_amount',
        'net_amount',
        'paid_amount',
        'remaining_amount',
        'status',
        'notes',
        'company_id',
        'creator_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'base_price' => 'decimal:2',
        'sibling_discount_pct' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tshirt_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'includes_tshirt' => 'boolean',
        'status' => SubscriptionStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->creator_id && Auth::check()) {
                $model->creator_id = Auth::id();
            }
        });
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function renewalOf(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'renewal_of_id');
    }

    public function renewedBy(): HasOne
    {
        return $this->hasOne(Subscription::class, 'renewal_of_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getDaysRemainingAttribute(): int
    {
        if (! $this->end_date) {
            return 0;
        }

        return (int) max(0, now()->diffInDays($this->end_date, false));
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return (float) $this->remaining_amount <= 0;
    }
}
