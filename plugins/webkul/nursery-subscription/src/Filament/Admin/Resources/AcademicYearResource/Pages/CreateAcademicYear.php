<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\AcademicYearResource;

class CreateAcademicYear extends CreateRecord
{
    protected static string $resource = AcademicYearResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
