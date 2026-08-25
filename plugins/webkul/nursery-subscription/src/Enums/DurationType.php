<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Enums;

/**
 * Enum for subscription duration types.
 */
enum DurationType: string
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
    public function label(): string
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
}
