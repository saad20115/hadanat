<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Enum for payment methods.
 */
enum PaymentMethod: string implements HasLabel
{
    case CASH = 'cash';
    case CARD = 'card';
    case BANK_TRANSFER = 'bank_transfer';
    case OTHER = 'other';

    /**
     * Get the Arabic label for the payment method.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::CASH          => '💵 نقدي',
            self::CARD          => '💳 بطاقة / مدى',
            self::BANK_TRANSFER => '🏦 حوالة بنكية',
            self::OTHER         => 'أخرى',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
