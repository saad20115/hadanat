<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcademicRulesAndLegendWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'nursery-subscription::filament.admin.widgets.academic-rules-and-legend';

    public string $term1_start = '08-30';
    public string $term1_end = '01-07';

    public string $term2_start = '01-17';
    public string $term2_end = '07-01';

    public string $yearly_start = '08-30';
    public string $yearly_end = '07-01';

    public int $expiring_soon_days = 7;
    public float $sibling_discount_pct = 5.00;
    public float $tshirt_price = 75.00;
    public string $paid_status_label = 'كامل';

    public function mount(): void
    {
        $companyId = Auth::user()?->default_company_id ?? 2;

        $saved = DB::table('settings')
            ->where('group', 'nursery_academic')
            ->where('company_id', $companyId)
            ->pluck('payload', 'name')
            ->toArray();

        if (isset($saved['term1_start'])) {
            $this->term1_start = json_decode($saved['term1_start'], true);
        }
        if (isset($saved['term1_end'])) {
            $this->term1_end = json_decode($saved['term1_end'], true);
        }
        if (isset($saved['term2_start'])) {
            $this->term2_start = json_decode($saved['term2_start'], true);
        }
        if (isset($saved['term2_end'])) {
            $this->term2_end = json_decode($saved['term2_end'], true);
        }
        if (isset($saved['yearly_start'])) {
            $this->yearly_start = json_decode($saved['yearly_start'], true);
        }
        if (isset($saved['yearly_end'])) {
            $this->yearly_end = json_decode($saved['yearly_end'], true);
        }
        if (isset($saved['expiring_soon_days'])) {
            $this->expiring_soon_days = (int) json_decode($saved['expiring_soon_days'], true);
        }
        if (isset($saved['sibling_discount_pct'])) {
            $this->sibling_discount_pct = (float) json_decode($saved['sibling_discount_pct'], true);
        }
        if (isset($saved['tshirt_price'])) {
            $this->tshirt_price = (float) json_decode($saved['tshirt_price'], true);
        }
        if (isset($saved['paid_status_label'])) {
            $this->paid_status_label = json_decode($saved['paid_status_label'], true);
        }
    }

    public function saveSettings(): void
    {
        $companyId = Auth::user()?->default_company_id ?? 2;

        $data = [
            'term1_start' => $this->term1_start,
            'term1_end' => $this->term1_end,
            'term2_start' => $this->term2_start,
            'term2_end' => $this->term2_end,
            'yearly_start' => $this->yearly_start,
            'yearly_end' => $this->yearly_end,
            'expiring_soon_days' => $this->expiring_soon_days,
            'sibling_discount_pct' => $this->sibling_discount_pct,
            'tshirt_price' => $this->tshirt_price,
            'paid_status_label' => $this->paid_status_label,
        ];

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(
                [
                    'group' => 'nursery_academic',
                    'name' => $key,
                    'company_id' => $companyId,
                ],
                [
                    'locked' => false,
                    'payload' => json_encode($value),
                    'updated_at' => now(),
                ]
            );
        }

        Notification::make()
            ->title('تم حفظ وتحديث القواعد الأكاديمية بنجاح')
            ->body('تم تطبيق التواريخ والخصومات وقواعد الحالات الجديدة على كامل النظام.')
            ->success()
            ->send();
    }
}
