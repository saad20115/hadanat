<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\NurseryCompanyResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryCompanyResource;

class EditNurseryCompany extends EditRecord
{
    protected static string $resource = NurseryCompanyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
