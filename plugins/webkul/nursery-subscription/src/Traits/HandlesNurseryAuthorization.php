<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Traits;

use Illuminate\Support\Facades\Auth;
use Webkul\Security\Models\User;

trait HandlesNurseryAuthorization
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false)) {
            return true;
        }

        $userPerms = $user->getAllPermissions()->pluck('name')->all();
        if (in_array('app_nursery', $userPerms, true)) {
            return true;
        }

        $normalizedRoles = $user->roles
            ->pluck('name')
            ->map(fn ($r) => strtolower(str_replace([' ', '_', '-'], '', (string) $r)))
            ->all();

        if (collect($normalizedRoles)->contains(fn ($r) => str_contains($r, 'nursery'))) {
            return true;
        }

        return null;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false)) {
            return true;
        }

        $userPerms = $user->getAllPermissions()->pluck('name')->all();
        if (in_array('app_nursery', $userPerms, true)) {
            return true;
        }

        $normalizedRoles = $user->roles
            ->pluck('name')
            ->map(fn ($r) => strtolower(str_replace([' ', '_', '-'], '', (string) $r)))
            ->all();

        return collect($normalizedRoles)->contains(fn ($r) => str_contains($r, 'nursery'));
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }
}
