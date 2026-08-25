<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Filament\Admin\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Webkul\NurserySubscription\Models\AcademicTerm;
use Webkul\NurserySubscription\Models\AcademicYear;
use Webkul\NurserySubscription\Models\AgeStageRule;
use Webkul\NurserySubscription\Models\Holiday;
use Webkul\Security\Models\User;
use Webkul\Support\Enums\NavigationGroup;

class NurserySettings extends Page
{
    protected static ?string $slug = 'nursery/settings';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 10;

    protected string $view = 'nursery-subscription::filament.admin.pages.nursery-settings';

    public string $activeTab = 'age_stages';

    // Age Stage Form State
    public ?int $editingStageId = null;
    public string $stage_name = '';
    public int $stage_min_age_months = 0;
    public int $stage_max_age_months = 36;
    public ?string $stage_description = null;
    public int $stage_sort_order = 1;
    public bool $stage_is_active = true;

    // Academic Year Form State
    public string $year_name = '';
    public string $year_start_date = '2026-08-30';
    public string $year_end_date = '2027-07-01';
    public bool $year_is_current = true;
    public ?string $year_notes = null;

    // Academic Term Form State
    public ?int $term_year_id = null;
    public string $term_name = '';
    public string $term_start_date = '2026-08-30';
    public string $term_end_date = '2027-01-07';
    public bool $term_is_current = false;

    // Holiday Form State
    public ?int $holiday_year_id = null;
    public string $holiday_name = '';
    public string $holiday_start_date = '2026-09-23';
    public string $holiday_end_date = '2026-09-23';
    public int $holiday_days_count = 1;
    public bool $holiday_affects_subs = false;
    public ?string $holiday_notes = null;

    // Subscription & Alert Rules
    public int $expiring_soon_days = 7;
    public float $sibling_discount_pct = 5.00;
    public float $tshirt_price = 75.00;
    public string $paid_status_label = 'كامل';

    // New User State
    public string $new_user_name = '';
    public string $new_user_email = '';
    public string $new_user_password = '';

    public function mount(): void
    {
        $companyId = $this->getCompanyId();

        // Load Rules from settings
        $saved = DB::table('settings')
            ->where('group', 'nursery_academic')
            ->where('company_id', $companyId)
            ->pluck('payload', 'name')
            ->toArray();

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

        $currentYear = AcademicYear::where('is_current', true)->first();
        if ($currentYear) {
            $this->term_year_id = $currentYear->id;
            $this->holiday_year_id = $currentYear->id;
        }
    }

    public static function getNavigationLabel(): string
    {
        return 'إعدادات الحضانة الشاملة';
    }

    public static function getNavigationGroup(): string|\UnitEnum
    {
        return NavigationGroup::Nursery;
    }

    public function getTitle(): string
    {
        return 'إعدادات الحضانة وفئات الأعمار الشاملة';
    }

    public function getSubheading(): ?string
    {
        return 'مركز التحكم الكامل بالتقويم الأكاديمي، فئات الأطفال، الإجازات، قواعد الاشتراكات، الحالات والمستخدمين';
    }

    protected function getCompanyId(): int
    {
        return (int) (Auth::user()?->default_company_id ?? 2);
    }

    // === TAB 1: AGE STAGE ACTIONS ===
    public function editStage(int $id): void
    {
        $stage = AgeStageRule::findOrFail($id);
        $this->editingStageId = $stage->id;
        $this->stage_name = $stage->name;
        $this->stage_min_age_months = $stage->min_age_months;
        $this->stage_max_age_months = $stage->max_age_months;
        $this->stage_description = $stage->description;
        $this->stage_sort_order = $stage->sort_order;
        $this->stage_is_active = $stage->is_active;
    }

    public function saveStage(): void
    {
        $this->validate([
            'stage_name' => 'required|string|max:100',
            'stage_min_age_months' => 'required|integer|min:0',
            'stage_max_age_months' => 'required|integer|gt:stage_min_age_months',
        ]);

        AgeStageRule::updateOrCreate(
            ['id' => $this->editingStageId],
            [
                'company_id' => $this->getCompanyId(),
                'name' => $this->stage_name,
                'min_age_months' => $this->stage_min_age_months,
                'max_age_months' => $this->stage_max_age_months,
                'description' => $this->stage_description,
                'sort_order' => $this->stage_sort_order,
                'is_active' => $this->stage_is_active,
            ]
        );

        $this->reset(['editingStageId', 'stage_name', 'stage_min_age_months', 'stage_max_age_months', 'stage_description']);
        $this->stage_sort_order = 1;
        $this->stage_is_active = true;

        Notification::make()->title('تم حفظ الفئة العمرية بنجاح')->success()->send();
    }

    public function deleteStage(int $id): void
    {
        AgeStageRule::destroy($id);
        Notification::make()->title('تم حذف الفئة العمرية')->success()->send();
    }

    // === TAB 2: ACADEMIC YEAR ACTIONS ===
    public function saveYear(): void
    {
        $this->validate([
            'year_name' => 'required|string|max:50',
            'year_start_date' => 'required|date',
            'year_end_date' => 'required|date|after:year_start_date',
        ]);

        if ($this->year_is_current) {
            AcademicYear::where('company_id', $this->getCompanyId())->update(['is_current' => false]);
        }

        AcademicYear::create([
            'company_id' => $this->getCompanyId(),
            'name' => $this->year_name,
            'start_date' => $this->year_start_date,
            'end_date' => $this->year_end_date,
            'is_current' => $this->year_is_current,
            'notes' => $this->year_notes,
        ]);

        $this->reset(['year_name', 'year_notes']);
        Notification::make()->title('تمت إضافة السنة الدراسية بنجاح')->success()->send();
    }

    public function setYearCurrent(int $id): void
    {
        AcademicYear::where('company_id', $this->getCompanyId())->update(['is_current' => false]);
        AcademicYear::where('id', $id)->update(['is_current' => true]);
        Notification::make()->title('تم تعيين السنة الدراسية كسنة حالية نشطة')->success()->send();
    }

    public function deleteYear(int $id): void
    {
        AcademicYear::destroy($id);
        Notification::make()->title('تم حذف السنة الدراسية')->success()->send();
    }

    // === TAB 2 (SUB): ACADEMIC TERM ACTIONS ===
    public function saveTerm(): void
    {
        $this->validate([
            'term_year_id' => 'required|exists:nursery_academic_years,id',
            'term_name' => 'required|string|max:100',
            'term_start_date' => 'required|date',
            'term_end_date' => 'required|date|after:term_start_date',
        ]);

        if ($this->term_is_current) {
            AcademicTerm::where('company_id', $this->getCompanyId())->update(['is_current' => false]);
        }

        AcademicTerm::create([
            'company_id' => $this->getCompanyId(),
            'academic_year_id' => $this->term_year_id,
            'name' => $this->term_name,
            'start_date' => $this->term_start_date,
            'end_date' => $this->term_end_date,
            'is_current' => $this->term_is_current,
        ]);

        $this->reset(['term_name']);
        Notification::make()->title('تمت إضافة الفصل الدراسي بنجاح')->success()->send();
    }

    public function deleteTerm(int $id): void
    {
        AcademicTerm::destroy($id);
        Notification::make()->title('تم حذف الفصل الدراسي')->success()->send();
    }

    // === TAB 3: HOLIDAY ACTIONS ===
    public function saveHoliday(): void
    {
        $this->validate([
            'holiday_name' => 'required|string|max:150',
            'holiday_start_date' => 'required|date',
            'holiday_end_date' => 'required|date|after_or_equal:holiday_start_date',
        ]);

        $start = \Carbon\Carbon::parse($this->holiday_start_date);
        $end = \Carbon\Carbon::parse($this->holiday_end_date);
        $days = (int) ($start->diffInDays($end) + 1);

        Holiday::create([
            'company_id' => $this->getCompanyId(),
            'academic_year_id' => $this->holiday_year_id,
            'name' => $this->holiday_name,
            'start_date' => $this->holiday_start_date,
            'end_date' => $this->holiday_end_date,
            'days_count' => $days,
            'affects_subscriptions' => $this->holiday_affects_subs,
            'notes' => $this->holiday_notes,
        ]);

        $this->reset(['holiday_name', 'holiday_notes']);
        Notification::make()->title('تمت إضافة الإجازة الدراسية بنجاح')->success()->send();
    }

    public function deleteHoliday(int $id): void
    {
        Holiday::destroy($id);
        Notification::make()->title('تم حذف الإجازة الدراسية')->success()->send();
    }

    // === TAB 4: SUBSCRIPTION & ALERTS RULES ACTIONS ===
    public function saveRules(): void
    {
        $companyId = $this->getCompanyId();

        $data = [
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
            ->title('تم حفظ وتحديث قواعد الاشتراكات والتنبيهات بنجاح')
            ->body('تم تطبيق التعديلات على كافة شاشات النظام والحاسبة.')
            ->success()
            ->send();
    }

    // === TAB 6: NEW USER ACTION ===
    public function saveNewUser(): void
    {
        $this->validate([
            'new_user_name' => 'required|string|max:255',
            'new_user_email' => 'required|email|unique:users,email',
            'new_user_password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $this->new_user_name,
            'email' => $this->new_user_email,
            'password' => Hash::make($this->new_user_password),
            'default_company_id' => $this->getCompanyId(),
            'is_active' => true,
        ]);

        $this->reset(['new_user_name', 'new_user_email', 'new_user_password']);
        Notification::make()->title('تم إنشاء حساب المستخدم بنجاح')->success()->send();
    }
}
