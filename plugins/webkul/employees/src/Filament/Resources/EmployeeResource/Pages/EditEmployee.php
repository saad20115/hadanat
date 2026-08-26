<?php

namespace Webkul\Employee\Filament\Resources\EmployeeResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Webkul\Chatter\Filament\Actions\ChatterAction;
use Webkul\Employee\Filament\Resources\EmployeeResource;
use Webkul\Support\Models\ActivityPlan;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getSavedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('employees::filament/resources/employee/pages/edit-employee.notification.title'))
            ->body(__('employees::filament/resources/employee/pages/edit-employee.notification.body'));
    }

    protected function getHeaderActions(): array
    {
        return [
            ChatterAction::make()
                ->resource(static::$resource)
                ->activityPlans($this->getActivityPlans()),
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('employees::filament/resources/employee/pages/edit-employee.header-actions.delete.notification.title'))
                        ->body(__('employees::filament/resources/employee/pages/edit-employee.header-actions.delete.notification.body')),
                ),
        ];
    }

    private function getActivityPlans(): mixed
    {
        return ActivityPlan::employees()->pluck('name', 'id');
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (! $this->record->partner_id || ! $this->record->partner) {
            $partner = \Webkul\Partner\Models\Partner::create([
                'name'         => $this->record->name,
                'email'        => $this->record->work_email ?? $this->record->private_email,
                'account_type' => 'individual',
                'sub_type'     => 'employee',
                'company_id'   => $this->record->company_id ?? 1,
                'creator_id'   => \Illuminate\Support\Facades\Auth::id(),
            ]);

            $this->record->partner_id = $partner->id;
            $this->record->saveQuietly();
            $this->record->refresh();
        }
    }

    protected function beforeSave(): void
    {
        if (! $this->record->partner_id || ! $this->record->partner) {
            $partner = \Webkul\Partner\Models\Partner::create([
                'name'         => $this->record->name,
                'email'        => $this->record->work_email ?? $this->record->private_email,
                'account_type' => 'individual',
                'sub_type'     => 'employee',
                'company_id'   => $this->record->company_id ?? 1,
                'creator_id'   => \Illuminate\Support\Facades\Auth::id(),
            ]);

            $this->record->partner_id = $partner->id;
            $this->record->saveQuietly();
        }
    }
}
