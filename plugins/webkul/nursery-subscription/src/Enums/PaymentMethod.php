<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Enums;

/**
 * Enum for payment methods.
 */
enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';
    case OTHER = 'other';

    /**
     * Get the Arabic label for the payment method.
     */
    public function label(): string
    {
        return match ($this) {
            self::CASH          => 'نقدي',
            self::CARD          => 'بطاقة ائتمان',
            self::BANK_TRANSFER => 'حوالة بنكية',
            self::OTHER         => 'أخرى',
        };
    }
}
