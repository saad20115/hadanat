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

        Gate::before(function ($user, $ability) {
            if (! $user) {
                return null;
            }

            if ($user->hasRole('super_admin') || $user->hasRole('Super_admin') || (bool) ($user->is_default ?? false)) {
                return true;
            }

            // Nursery specific roles bypass
            if (str_contains($ability, 'nursery')) {
                if ($user->hasRole(['nursery_manager', 'nursery_supervisor', 'nursery_registrar', 'nursery_accountant'])) {
                    return true;
                }
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
                if (str_contains($ability, $keyword) && $ability !== $perm) {
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
