<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Webkul\PluginManager\Package;

class NurserySubscriptionPlugin implements Plugin
{
    public function getId(): string
    {
        return 'nursery-subscription';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        if (! Package::isPluginInstalled($this->getId())) {
            return;
        }

        $panel->when($panel->getId() === 'admin', function (Panel $panel) {
            $panel->discoverResources(
                in: __DIR__ . '/Filament/Admin/Resources',
                for: 'Webkul\\NurserySubscription\\Filament\\Admin\\Resources'
            )
            ->discoverPages(
                in: __DIR__ . '/Filament/Admin/Pages',
                for: 'Webkul\\NurserySubscription\\Filament\\Admin\\Pages'
            )
            ->discoverClusters(
                in: __DIR__ . '/Filament/Admin/Clusters',
                for: 'Webkul\\NurserySubscription\\Filament\\Admin\\Clusters'
            )
            ->discoverWidgets(
                in: __DIR__ . '/Filament/Admin/Widgets',
                for: 'Webkul\\NurserySubscription\\Filament\\Admin\\Widgets'
            );
        });

        $panel->when($panel->getId() === 'customer', function (Panel $panel) {
            // Register future Guardian Portal resources here
        });
    }

    public function boot(Panel $panel): void
    {
    }
}
