<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Enums\PaymentMethod;
use Webkul\Security\Models\User;
use Webkul\Support\Traits\BelongsToCompany;

class Payment extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'nursery_payments';

    protected $fillable = [
        'subscription_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'notes',
        'company_id',
        'creator_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_method' => PaymentMethod::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->creator_id && Auth::check()) {
                $model->creator_id = Auth::id();
            }
        });

        static::created(function ($payment) {
            $subscription = $payment->subscription;
            if ($subscription) {
                $subscription->paid_amount += $payment->amount;
                $subscription->remaining_amount = $subscription->net_amount - $subscription->paid_amount;
                $subscription->save();
            }
        });
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
