<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\PricingPlanResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\PricingPlanResource;

class ListPricingPlans extends ListRecords
{
    protected static string $resource = PricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة باقة جديدة')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
