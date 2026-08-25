<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('اشتراك جديد')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
