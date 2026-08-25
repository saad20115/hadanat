<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryUserResource;
use Webkul\Support\Services\CompanyContext;

class CreateNurseryUser extends CreateRecord
{
    protected static string $resource = NurseryUserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $companyId = app(CompanyContext::class)->currentId() ?? Auth::user()?->default_company_id ?? 1;
        $data['default_company_id'] = $companyId;

        $record = parent::handleRecordCreation($data);

        if (method_exists($record, 'allowedCompanies')) {
            $record->allowedCompanies()->syncWithoutDetaching([$companyId]);
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
