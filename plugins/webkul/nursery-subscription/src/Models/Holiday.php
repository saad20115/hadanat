<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Support\Traits\BelongsToCompany;

class Holiday extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'nursery_holidays';

    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'days_count',
        'affects_subscriptions',
        'notes',
        'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'affects_subscriptions' => 'boolean',
        'days_count' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
