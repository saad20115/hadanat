<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\NurserySubscription\Filament\Admin\Resources\PaymentResource;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
