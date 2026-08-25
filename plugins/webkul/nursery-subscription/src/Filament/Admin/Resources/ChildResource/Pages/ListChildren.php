<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource;

class ListChildren extends ListRecords
{
    protected static string $resource = ChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('تسجيل طفل جديد')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
