<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Clusters;

use Filament\Clusters\Cluster;
use Webkul\Support\Enums\NavigationGroup;

class Configurations extends Cluster
{
    protected static ?string $slug = 'nursery/configurations';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return 'الإعدادات';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return 'الإعدادات';
    }
}
