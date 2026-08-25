<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource;

class CreateNurseryUser extends CreateRecord
{
    protected static string $resource = NurseryUserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['default_company_id'] = Auth::user()?->default_company_id ?? 2;

        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
