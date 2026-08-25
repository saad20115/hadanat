<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\NurseryCompanyResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Webkul\NurserySubscription\Filament\Admin\Resources\NurseryCompanyResource;

class ListNurseryCompanies extends ListRecords
{
    protected static string $resource = NurseryCompanyResource::class;
}
