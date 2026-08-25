<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\HolidayResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\HolidayResource;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة إجازة دراسية جديدة')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
