<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Enum for subscription duration types.
 */
enum DurationType: string implements HasColor, HasLabel
{
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case TERM = 'term';
    case YEARLY = 'yearly';
    case VISIT_PACKAGE = 'visit_package';

    /**
     * Get the Arabic label for the duration type.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::HOURLY        => 'بالساعة',
            self::DAILY         => 'يومي',
            self::WEEKLY        => 'أسبوعي',
            self::MONTHLY       => 'شهري',
            self::TERM          => 'فصل دراسي',
            self::YEARLY        => 'سنوي',
            self::VISIT_PACKAGE => 'باقة زيارات',
        };
    }

    /**
     * Get the Filament badge color.
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HOURLY        => 'gray',
            self::DAILY         => 'info',
            self::WEEKLY        => 'primary',
            self::MONTHLY       => 'success',
            self::TERM          => 'warning',
            self::YEARLY        => 'danger',
            self::VISIT_PACKAGE => 'info',
        };
    }
}
