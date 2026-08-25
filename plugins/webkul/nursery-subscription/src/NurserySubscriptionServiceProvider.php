<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription;

use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use Webkul\NurserySubscription\Console\Commands\UpdateSubscriptionStatuses;
use Webkul\NurserySubscription\Models\AcademicYear;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\Holiday;
use Webkul\NurserySubscription\Models\Payment;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Models\Subscription;
use Webkul\NurserySubscription\Policies\AcademicYearPolicy;
use Webkul\NurserySubscription\Policies\AgeStageRulePolicy;
use Webkul\NurserySubscription\Policies\ChildPolicy;
use Webkul\NurserySubscription\Policies\HolidayPolicy;
use Webkul\NurserySubscription\Policies\PaymentPolicy;
use Webkul\NurserySubscription\Policies\PricingPlanPolicy;
use Webkul\NurserySubscription\Policies\SubscriptionPolicy;
use Webkul\NurserySubscription\Services\PricingAndLifecycleService;
use Webkul\PluginManager\Console\Commands\InstallCommand;
use Webkul\PluginManager\Console\Commands\UninstallCommand;
use Webkul\PluginManager\Package;
use Webkul\PluginManager\PackageServiceProvider;

class NurserySubscriptionServiceProvider extends PackageServiceProvider
{
    public static string $name = 'nursery-subscription';

    public static string $viewNamespace = 'nursery-subscription';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                '0001_01_01_000001_create_nursery_children_table',
                '0001_01_01_000002_create_nursery_pricing_plans_table',
                '0001_01_01_000003_create_nursery_subscriptions_table',
                '0001_01_01_000004_create_nursery_payments_table',
                '0001_01_01_000005_create_nursery_age_stages_table',
                '0001_01_01_000006_create_nursery_academic_calendar_tables',
            ])
            ->runsMigrations()
            ->hasSettings([])
            ->runsSettings()
            ->hasSeeder('Webkul\\NurserySubscription\\Database\\Seeders\\NurserySubscriptionDatabaseSeeder')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->runsMigrations()
                    ->runsSeeders();
            })
            ->hasUninstallCommand(function (UninstallCommand $command) {})
            ->icon('nursery-subscription');
    }

    public function packageBooted(): void
    {
        $this->commands([
            UpdateSubscriptionStatuses::class,
        ]);

        Gate::policy(Child::class, ChildPolicy::class);
        Gate::policy(PricingPlan::class, PricingPlanPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(AgeStageRule::class, AgeStageRulePolicy::class);
        Gate::policy(AcademicYear::class, AcademicYearPolicy::class);
        Gate::policy(Holiday::class, HolidayPolicy::class);
    }

    public function packageRegistered(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(NurserySubscriptionPlugin::make());
        });

        $this->app->singleton(PricingAndLifecycleService::class);
    }
}
