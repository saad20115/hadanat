<?php

declare(strict_types=1);

use Webkul\Security\Models\Role;
use Webkul\Security\Models\User;

it('ensures getLandingPageForUser returns correct configured role landing page', function () {
    $role = Mockery::mock(Role::class)->makePartial();
    $role->default_landing_page = 'nursery/subscriptions';

    $user = Mockery::mock(User::class)->makePartial();
    $user->roles = collect([$role]);

    $url = Role::getLandingPageForUser($user);
    expect($url)->toBe('/admin/nursery/subscriptions');
});

it('ensures getLandingPageForUser falls back to nursery subscriptions if user has app_nursery permission', function () {
    $user = Mockery::mock(User::class)->makePartial();
    $user->roles = collect();
    $user->shouldReceive('can')->with('app_nursery')->andReturn(true);
    $user->shouldReceive('can')->with('app_security')->andReturn(false);
    $user->shouldReceive('hasRole')->andReturn(false);

    $url = Role::getLandingPageForUser($user);
    expect($url)->toBe('/admin/nursery/subscriptions');
});
