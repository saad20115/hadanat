<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource;

class EditAgeStageRule extends EditRecord
{
    protected static string $resource = AgeStageRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
