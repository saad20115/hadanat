<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;
use Webkul\Support\Models\Company;
use Webkul\Support\Traits\BelongsToCompany;

class AgeStageRule extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'nursery_age_stages';

    protected $fillable = [
        'company_id',
        'creator_id',
        'code',
        'name',
        'min_age_months',
        'max_age_months',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_age_months' => 'integer',
        'max_age_months' => 'integer',
        'is_active'      => 'boolean',
        'sort_order'     => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (! $model->creator_id && Auth::check()) {
                $model->creator_id = Auth::id();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getAgeRangeLabelAttribute(): string
    {
        $minYears = round($this->min_age_months / 12, 1);
        $maxYears = round($this->max_age_months / 12, 1);

        return sprintf(
            'من %d شهر (%s سنة) إلى %d شهر (%s سنة)',
            $this->min_age_months,
            $minYears,
            $this->max_age_months,
            $maxYears
        );
    }

    public function matchesMonths(int $months): bool
    {
        return $months >= $this->min_age_months && $months < $this->max_age_months;
    }
}
