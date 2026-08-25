<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Webkul\NurserySubscription\Filament\Admin\Resources\SubscriptionResource;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Services\PricingAndLifecycleService;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(PricingAndLifecycleService::class);
        $child = Child::findOrFail($data['child_id']);
        $plan = PricingPlan::findOrFail($data['pricing_plan_id']);
        
        return $service->createSubscription(
            child: $child,
            plan: $plan,
            startDate: \Carbon\Carbon::parse($data['start_date']),
            includeTshirt: $data['includes_tshirt'] ?? false,
            initialPayment: !empty($data['initial_payment']) ? (float) $data['initial_payment'] : null,
            paymentMethod: $data['payment_method'] ?? null,
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
