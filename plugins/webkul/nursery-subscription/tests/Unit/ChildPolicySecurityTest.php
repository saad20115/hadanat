<?php

declare(strict_types=1);

use Webkul\NurserySubscription\Policies\ChildPolicy;
use Webkul\NurserySubscription\Policies\SubscriptionPolicy;
use Webkul\Security\Models\User;

it('ensures regular active users do not automatically bypass child policy without permissions', function () {
    $policy = new ChildPolicy;

    $regularUser = Mockery::mock(User::class)->makePartial();
    $regularUser->is_default = false;
    $regularUser->is_active = true;
    $regularUser->shouldReceive('hasRole')->andReturn(false);

    $result = $policy->before($regularUser, 'delete');
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

it('ensures subscription policy does not grant automatic bypass to regular active user', function () {
    $policy = new SubscriptionPolicy;

    $regularUser = Mockery::mock(User::class)->makePartial();
    $regularUser->is_default = false;
    $regularUser->is_active = true;
    $regularUser->shouldReceive('hasRole')->andReturn(false);

    $result = $policy->before($regularUser, 'delete');
    expect($result)->toBeNull();
});
