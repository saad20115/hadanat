<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource;

class ListAcademicYears extends ListRecords
{
    protected static string $resource = AcademicYearResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('إضافة سنة دراسية جديدة')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
