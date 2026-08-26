<?php

declare(strict_types=1);

use Webkul\NurserySubscription\Policies\ChildPolicy;
use Webkul\NurserySubscription\Policies\SubscriptionPolicy;
use Webkul\Security\Models\User;

it('ensures null user returns null in child policy', function () {
    $policy = new ChildPolicy;

    $result = $policy->before(null, 'delete');
    expect($result)->toBeNull();
});

it('ensures super admin or default user bypasses policy checks', function () {
    $policy = new ChildPolicy;

    $defaultUser = Mockery::mock(User::class)->makePartial();
    $defaultUser->is_default = true;
    $defaultUser->is_active = true;
    $defaultUser->shouldReceive('hasRole')->andReturn(false);

    $result = $policy->before($defaultUser, 'delete');
    expect($result)->toBeTrue();
});

it('ensures super admin role bypasses policy checks', function () {
    $policy = new ChildPolicy;

    $superAdminUser = Mockery::mock(User::class)->makePartial();
    $superAdminUser->is_default = false;
    $superAdminUser->is_active = true;
    $superAdminUser->shouldReceive('hasRole')->andReturn(true);

    $result = $policy->before($superAdminUser, 'delete');
    expect($result)->toBeTrue();
});

it('ensures subscription policy returns null for null user', function () {
    $policy = new SubscriptionPolicy;

    $result = $policy->before(null, 'delete');
    expect($result)->toBeNull();
});
