<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\PaymentResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\PaymentResource;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('تسجيل دفعة جديدة')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
