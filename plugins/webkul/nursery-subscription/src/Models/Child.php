<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Enums\AgeStage;
use Webkul\Security\Models\User;
use Webkul\Support\Traits\BelongsToCompany;

class Child extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $table = 'nursery_children';

    protected $fillable = [
        'full_name',
        'birth_date',
        'gender',
        'guardian_name',
        'guardian_phone',
        'emergency_contact',
        'emergency_phone',
        'has_siblings',
        'medical_notes',
        'notes',
        'company_id',
        'creator_id',
    ];

    protected $casts = [
        'birth_date'   => 'date',
        'has_siblings' => 'boolean',
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'expiring_soon'])
            ->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getAgeInMonthsAttribute(): int
    {
        if (! $this->birth_date) {
            return 0;
        }

        return (int) Carbon::now()->diffInMonths($this->birth_date);
    }

    public function getAgeStageAttribute(): ?AgeStage
    {
        if (! $this->birth_date) {
            return null;
        }

        $months = (int) Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());

        $customRule = AgeStageRule::where('company_id', $this->company_id)
            ->where('is_active', true)
            ->where('min_age_months', '<=', $months)
            ->where('max_age_months', '>', $months)
            ->orderBy('sort_order')
            ->first();

        if ($customRule && ($enum = AgeStage::tryFrom($customRule->code))) {
            return $enum;
        }

        try {
            return AgeStage::fromBirthDate(Carbon::parse($this->birth_date));
        } catch (\Throwable) {
            return null;
        }
    }

    public function getAgeStageLabelAttribute(): string
    {
        if (! $this->birth_date) {
            return 'غير محدد';
        }

        $months = (int) Carbon::parse($this->birth_date)->diffInMonths(Carbon::now());
        $customRule = AgeStageRule::where('company_id', $this->company_id)
            ->where('is_active', true)
            ->where('min_age_months', '<=', $months)
            ->where('max_age_months', '>', $months)
            ->orderBy('sort_order')
            ->first();

        if ($customRule) {
            return $customRule->name;
        }

        return $this->age_stage ? $this->age_stage->label() : 'غير محدد';
    }

    public function getAgeLabelAttribute(): string
    {
        if (! $this->birth_date) {
            return 'غير محدد';
        }

        $birthDate = Carbon::parse($this->birth_date);
        $now = Carbon::now();

        $years = (int) $birthDate->diffInYears($now);
        $months = (int) ($birthDate->diffInMonths($now) % 12);

        $label = [];
        if ($years > 0) {
            $label[] = $years.' '.($years == 1 ? 'سنة' : ($years == 2 ? 'سنتان' : ($years <= 10 ? 'سنوات' : 'سنة')));
        }
        if ($months > 0) {
            $label[] = $months.' '.($months == 1 ? 'شهر' : ($months == 2 ? 'شهران' : ($months <= 10 ? 'أشهر' : 'شهراً')));
        }

        return empty($label) ? 'أقل من شهر' : implode(' و ', $label);
    }
}
