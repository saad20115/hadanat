<?php

declare(strict_types=1);

use Carbon\Carbon;
use Webkul\NurserySubscription\Enums\AgeStage;

it('correctly determines infant stage for 6 to 17 months', function () {
    $birthDate = Carbon::today()->subMonths(10);
    $stage = AgeStage::fromBirthDate($birthDate);
    expect($stage)->toBe(AgeStage::INFANT);
    expect($stage->label())->toBe('الرضع');
});

it('correctly determines toddler stage for 18 to 35 months', function () {
    $birthDate = Carbon::today()->subMonths(24);
    $stage = AgeStage::fromBirthDate($birthDate);
    expect($stage)->toBe(AgeStage::TODDLER);
    expect($stage->label())->toBe('البراعم');
});

it('correctly determines KG stage for 36 to 72 months', function () {
    $birthDate = Carbon::today()->subMonths(48);
    $stage = AgeStage::fromBirthDate($birthDate);
    expect($stage)->toBe(AgeStage::KG);
    expect($stage->label())->toBe('رياض الأطفال');
});

it('throws exception for age under 6 months', function () {
    $birthDate = Carbon::today()->subMonths(3);
    AgeStage::fromBirthDate($birthDate);
})->throws(InvalidArgumentException::class);

it('throws exception for age over 6 years', function () {
    $birthDate = Carbon::today()->subYears(7);
    AgeStage::fromBirthDate($birthDate);
})->throws(InvalidArgumentException::class);
