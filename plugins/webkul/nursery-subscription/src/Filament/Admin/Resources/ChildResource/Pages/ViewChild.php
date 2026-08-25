<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\ChildResource;

class ViewChild extends ViewRecord
{
    protected static string $resource = ChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
