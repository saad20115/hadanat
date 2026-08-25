<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Support\Traits\BelongsToCompany;

class AcademicYear extends Model
{
    use BelongsToCompany, HasFactory;

    protected $table = 'nursery_academic_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current',
        'notes',
        'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function terms(): HasMany
    {
        return $this->hasMany(AcademicTerm::class, 'academic_year_id')->orderBy('start_date');
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class, 'academic_year_id')->orderBy('start_date');
    }
}
