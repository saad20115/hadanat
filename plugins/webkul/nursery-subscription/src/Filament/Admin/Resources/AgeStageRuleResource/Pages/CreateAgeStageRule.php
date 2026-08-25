<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\AgeStageRuleResource;

class CreateAgeStageRule extends CreateRecord
{
    protected static string $resource = AgeStageRuleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
