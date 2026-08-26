<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Enums;

use Carbon\Carbon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use InvalidArgumentException;

/**
 * Enum for nursery age stages.
 */
enum AgeStage: string implements HasColor, HasLabel
{
    case INFANT = 'infant';
    case TODDLER = 'toddler';
    case KG = 'kg';

    /**
     * Get the Arabic label for the age stage.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::INFANT  => 'الرضع',
            self::TODDLER => 'البراعم',
            self::KG      => 'رياض الأطفال',
        };
    }

    /**
     * Get the Filament badge color.
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INFANT  => 'info',
            self::TODDLER => 'warning',
            self::KG      => 'success',
        };
    }

    /**
     * Get the age range description.
     */
    public function ageRange(): string
    {
        return match ($this) {
            self::INFANT  => '6-18 months',
            self::TODDLER => '18 months - 3 years',
            self::KG      => '3-6 years',
        };
    }

    /**
     * Calculate the age stage from a given birth date.
     *
     * @throws InvalidArgumentException
     */
    public static function fromBirthDate(Carbon $birthDate): self
    {
        $months = $birthDate->diffInMonths(Carbon::now());

        if ($months >= 6 && $months <= 17) {
            return self::INFANT;
        }

        if ($months >= 18 && $months <= 35) {
            return self::TODDLER;
        }

        if ($months >= 36 && $months <= 72) {
            return self::KG;
        }

        throw new InvalidArgumentException('Birth date is outside the accepted nursery age ranges.');
    }
}
