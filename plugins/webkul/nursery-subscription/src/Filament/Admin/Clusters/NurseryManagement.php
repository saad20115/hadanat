<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Clusters;

use Filament\Clusters\Cluster;

class NurseryManagement extends Cluster
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    
    protected static string | \UnitEnum | null $navigationGroup = 'إدارة الحضانة';
    
    protected static ?int $navigationSort = 50;

    public static function getNavigationLabel(): string
    {
        return 'إدارة الحضانة';
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return 'إدارة الحضانة';
    }
}
