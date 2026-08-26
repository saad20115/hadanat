<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Component;
use Livewire\Livewire;
use Webkul\Security\Models\User;

use function Livewire\on;
use function Livewire\store;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Authenticatable::class, User::class);
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if (! $user) {
                return null;
            }

            if ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false)) {
                return true;
            }

            // Normalize user role names (handles "Nursery Manager", "nursery_manager", "NURSERY_SUPERVISOR", etc.)
            $normalizedRoles = $user->roles
                ->pluck('name')
                ->map(fn ($r) => strtolower(str_replace([' ', '_', '-'], '', (string) $r)))
                ->all();

            $isNurseryUser = in_array('superadmin', $normalizedRoles, true)
                || collect($normalizedRoles)->contains(fn ($r) => str_contains($r, 'nursery'))
                || $user->can('app_nursery');

            // Grant all nursery abilities if user is associated with nursery role or app_nursery
            if ($isNurseryUser && str_contains(strtolower($ability), 'nursery')) {
                return true;
            }

            // Module-level permission bypass
            $moduleMap = [
                'nursery'     => 'app_nursery',
                'sales'       => 'app_sales',
                'sale'        => 'app_sales',
                'purchase'    => 'app_purchases',
                'invoice'     => 'app_invoices',
                'account'     => 'app_accounts',
                'employee'    => 'app_employees',
                'recruitment' => 'app_recruitments',
                'time_off'    => 'app_time_off',
                'project'     => 'app_projects',
                'maintenance' => 'app_maintenance',
                'website'     => 'app_website',
                'contact'     => 'app_contacts',
                'plugin'      => 'app_plugins',
                'security'    => 'app_security',
            ];

            foreach ($moduleMap as $keyword => $perm) {
                if (str_contains(strtolower($ability), $keyword) && $ability !== $perm) {
                    if ($user->can($perm)) {
                        return true;
                    }
                }
            }

            return null;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        on('dehydrate', function (Component $component): void {
            if (! Livewire::isLivewireRequest()) {
                return;
            }

            if (! store($component)->has('redirect')) {
                return;
            }

            $notifications = session()->pull('filament.notifications');

            if (empty($notifications)) {
                return;
            }

            session()->put('filament.claimed_notifications', $notifications);
        });
    }
}
