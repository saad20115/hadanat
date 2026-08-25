<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource;

class ListAgeStageRules extends ListRecords
{
    protected static string $resource = AgeStageRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('تعريف فئة عمرية جديدة')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
