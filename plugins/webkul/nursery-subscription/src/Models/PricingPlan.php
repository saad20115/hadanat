<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\NurserySubscription\Enums\AgeStage;
use Webkul\NurserySubscription\Enums\DurationType;
use Webkul\Support\Traits\BelongsToCompany;

class PricingPlan extends Model
{
    use HasFactory, BelongsToCompany;

    protected $table = 'nursery_pricing_plans';

    protected $fillable = [
        'age_stage',
        'stage_label',
        'duration_type',
        'hours_per_day',
        'duration_value',
        'duration_label',
        'visits_count',
        'visits_period_months',
        'price',
        'is_active',
        'sort_order',
        'company_id',
    ];

    protected $casts = [
        'age_stage' => AgeStage::class,
        'duration_type' => DurationType::class,
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForAgeStage(Builder $query, AgeStage $stage): void
    {
        $query->where('age_stage', $stage);
    }
}
