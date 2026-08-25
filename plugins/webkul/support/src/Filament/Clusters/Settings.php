<?php

namespace Webkul\Support\Filament\Clusters;

use Filament\Clusters\Cluster;
use Illuminate\Support\Facades\Auth;
use Webkul\Support\Enums\NavigationGroup;

class Settings extends Cluster
{
    protected static ?int $navigationSort = 1000;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('Super_admin') || $user->hasRole('super_admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('Super_admin') || $user->hasRole('super_admin');
    }

    public static function canAccessClusteredComponents(): bool
    {
        $user = Auth::user();

        if (! $user || (! $user->hasRole('Super_admin') && ! $user->hasRole('super_admin'))) {
            return false;
        }

        foreach (static::getClusteredComponents() as $component) {
            if ($component::shouldRegisterNavigation() && $component::canAccess()) {
                return true;
            }
        }

        return false;
    }

    public static function getNavigationLabel(): string
    {
        return __('support::filament/clusters/settings/pages/settings.navigation.title');
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Setting;
    }
}
