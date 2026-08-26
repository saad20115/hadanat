<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait HandlesNurseryAuthorization
{
    public function before($user, string $ability): ?bool
    {
        if (! $user) {
            return null;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false))) {
            return true;
        }

        if (method_exists($user, 'getAllPermissions')) {
            $userPerms = $user->getAllPermissions()->pluck('name')->all();
            if (in_array('app_nursery', $userPerms, true)) {
                return true;
            }
        }

        if (isset($user->roles)) {
            $normalizedRoles = $user->roles
                ->pluck('name')
                ->map(fn ($r) => strtolower(str_replace([' ', '_', '-'], '', (string) $r)))
                ->all();

            if (collect($normalizedRoles)->contains(fn ($r) => str_contains($r, 'nursery'))) {
                return true;
            }
        }

        return true;
    }

    public static function checkNurseryAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false))) {
            return true;
        }

        if (method_exists($user, 'getAllPermissions')) {
            $userPerms = $user->getAllPermissions()->pluck('name')->all();
            if (in_array('app_nursery', $userPerms, true)) {
                return true;
            }
        }

        if (isset($user->roles)) {
            $normalizedRoles = $user->roles
                ->pluck('name')
                ->map(fn ($r) => strtolower(str_replace([' ', '_', '-'], '', (string) $r)))
                ->all();

            if (collect($normalizedRoles)->contains(fn ($r) => str_contains($r, 'nursery'))) {
                return true;
            }
        }

        return true;
    }

    public static function canAccess(): bool
    {
        return static::checkNurseryAccess();
    }

    public static function canViewAny(): bool
    {
        return static::checkNurseryAccess();
    }

    public static function canCreate(): bool
    {
        return static::checkNurseryAccess();
    }

    public static function canEdit(Model $record): bool
    {
        return static::checkNurseryAccess();
    }

    public static function canDelete(Model $record): bool
    {
        return static::checkNurseryAccess();
    }
}
