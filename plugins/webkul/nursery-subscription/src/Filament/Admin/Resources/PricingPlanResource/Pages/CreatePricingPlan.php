<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\PricingPlanResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\PricingPlanResource;

class CreatePricingPlan extends CreateRecord
{
    protected static string $resource = PricingPlanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
