<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Enums;

/**
 * Enum for subscription statuses.
 */
enum SubscriptionStatus: string
{
    case NEW = 'new';
    case ACTIVE = 'active';
    case EXPIRING_SOON = 'expiring_soon';
    case EXPIRED = 'expired';
    case RENEWED = 'renewed';
    case CANCELLED = 'cancelled';
    case FROZEN = 'frozen';

    /**
     * Get the Arabic label for the subscription status.
     */
    public function label(): string
    {
        return match($this) {
            self::NEW => 'جديد',
            self::ACTIVE => 'ساري',
            self::EXPIRING_SOON => 'قرب ينتهي',
            self::EXPIRED => 'منتهي',
            self::RENEWED => 'مجدد',
            self::CANCELLED => 'ملغي',
            self::FROZEN => 'مجمّد',
        };
    }

    /**
     * Get the Filament badge color.
     */
    public function color(): string
    {
        return match($this) {
            self::NEW => 'info',
            self::ACTIVE => 'success',
            self::EXPIRING_SOON => 'warning',
            self::EXPIRED => 'danger',
            self::RENEWED => 'primary',
            self::CANCELLED => 'gray',
            self::FROZEN => 'gray',
        };
    }

    /**
     * Get the heroicon name for the status.
     */
    public function icon(): string
    {
        return match($this) {
            self::NEW => 'heroicon-m-sparkles',
            self::ACTIVE => 'heroicon-m-check-circle',
            self::EXPIRING_SOON => 'heroicon-m-clock',
            self::EXPIRED => 'heroicon-m-x-circle',
            self::RENEWED => 'heroicon-m-arrow-path',
            self::CANCELLED => 'heroicon-m-no-symbol',
            self::FROZEN => 'heroicon-m-pause-circle',
        };
    }
}
