<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
