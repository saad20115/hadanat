<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Clusters;

use Filament\Clusters\Cluster;
use Webkul\Support\Enums\NavigationGroup;

class NurseryManagement extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public static function getNavigationLabel(): string
    {
        return 'براعم';
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return 'براعم';
    }
}
