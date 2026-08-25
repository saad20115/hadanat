<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource;

class ListNurseryUsers extends ListRecords
{
    protected static string $resource = NurseryUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة مستخدم جديد')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
