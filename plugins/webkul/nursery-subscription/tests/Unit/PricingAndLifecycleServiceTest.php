<?php

declare(strict_types=1);

use Carbon\Carbon;
use Webkul\NurserySubscription\Enums\DurationType;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Services\PricingAndLifecycleService;

it('calculates correct end date for daily plan', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type' => DurationType::DAILY,
    ]);

    $start = Carbon::parse('2026-09-01');
    $end = $service->calculateEndDate($plan, $start);

    expect($end->format('Y-m-d'))->toBe('2026-09-01');
});

it('calculates correct end date for weekly plan (1 week)', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type'  => DurationType::WEEKLY,
        'duration_value' => 1,
    ]);

    $start = Carbon::parse('2026-09-01');
    $end = $service->calculateEndDate($plan, $start);

    expect($end->format('Y-m-d'))->toBe('2026-09-07');
});

it('calculates correct end date for 1 month plan', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type'  => DurationType::MONTHLY,
        'duration_value' => 1,
    ]);

    $start = Carbon::parse('2026-09-01');
    $end = $service->calculateEndDate($plan, $start);

    expect($end->format('Y-m-d'))->toBe('2026-09-30');
});

it('calculates correct end date for 3 months plan', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type'  => DurationType::MONTHLY,
        'duration_value' => 3,
    ]);

    $start = Carbon::parse('2026-09-01');
    $end = $service->calculateEndDate($plan, $start);

    expect($end->format('Y-m-d'))->toBe('2026-11-30');
});

it('calculates correct end date for term 1 (4 months and 1 week)', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type'  => DurationType::TERM,
        'duration_value' => 4.25,
    ]);

    $start = Carbon::parse('2026-09-01');
    $end = $service->calculateEndDate($plan, $start);

    // 2026-09-01 + 4 months (2027-01-01) + 1 week (2027-01-08) - 1 day = 2027-01-07
    expect($end->format('Y-m-d'))->toBe('2027-01-07');
});

it('calculates correct pricing without discounts or tshirt', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type' => DurationType::MONTHLY,
        'price'         => 2070.00,
    ]);

    $child = new Child([
        'has_siblings' => false,
    ]);

    $result = $service->calculateNetAmount($plan, $child, false);

    expect($result['base_price'])->toBe(2070.00);
    expect($result['sibling_discount_pct'])->toBe(0.00);
    expect($result['discount_amount'])->toBe(0.00);
    expect($result['tshirt_amount'])->toBe(0.00);
    expect($result['net_amount'])->toBe(2070.00);
});

it('calculates correct pricing with tshirt fee', function () {
    $service = new PricingAndLifecycleService;
    $plan = new PricingPlan([
        'duration_type' => DurationType::MONTHLY,
        'price'         => 2070.00,
    ]);

    $child = new Child([
        'has_siblings' => false,
    ]);

    $result = $service->calculateNetAmount($plan, $child, true);

    expect($result['tshirt_amount'])->toBe(75.00);
    expect($result['net_amount'])->toBe(2145.00);
});
