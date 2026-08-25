<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource;

class CreateChild extends CreateRecord
{
    protected static string $resource = ChildResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
