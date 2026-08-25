<x-filament-panels::page>
    @php
        $companyId = (int) (auth()->user()?->default_company_id ?? 2);
        $stages = \Webkul\NurserySubscription\Models\AgeStageRule::where('company_id', $companyId)->orderBy('sort_order')->get();
        $years = \Webkul\NurserySubscription\Models\AcademicYear::where('company_id', $companyId)->with(['terms', 'holidays'])->orderByDesc('start_date')->get();
        $terms = \Webkul\NurserySubscription\Models\AcademicTerm::where('company_id', $companyId)->with('academicYear')->orderBy('start_date')->get();
        $holidays = \Webkul\NurserySubscription\Models\Holiday::where('company_id', $companyId)->with('academicYear')->orderBy('start_date')->get();
        $users = \Webkul\Security\Models\User::where('default_company_id', $companyId)->orWhere('id', auth()->id())->get();
    @endphp

    <!-- Navigation Tabs Bar (Filament Unified Style) -->
    <div class="fi-tabs flex gap-x-1 border-b border-gray-200 dark:border-white/10 pb-px">
        @foreach([
            'age_stages' => ['label' => 'فئات الأعمار والأقسام', 'icon' => 'heroicon-o-user-group', 'count' => $stages->count()],
            'academic_calendar' => ['label' => 'السنوات والفصول الدراسية', 'icon' => 'heroicon-o-academic-cap', 'count' => $years->count()],
            'holidays' => ['label' => 'الإجازات والعطل الدراسية', 'icon' => 'heroicon-o-calendar', 'count' => $holidays->count()],
            'subscription_rules' => ['label' => 'قواعد الاشتراكات والتنبيهات', 'icon' => 'heroicon-o-adjustments-horizontal', 'count' => null],
            'statuses_colors' => ['label' => 'دليل الحالات والألوان', 'icon' => 'heroicon-o-swatch', 'count' => null],
            'users_roles' => ['label' => 'المستخدمين والصلاحيات', 'icon' => 'heroicon-o-users', 'count' => $users->count()],
        ] as $tabKey => $tab)
            <button
                type="button"
                wire:click="$set('activeTab', '{{ $tabKey }}')"
                class="fi-tabs-item flex items-center gap-x-2 px-3 py-2 text-sm font-medium transition duration-75 border-b-2 -mb-px {{ $activeTab === $tabKey ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200' }}"
            >
                <x-filament::icon :icon="$tab['icon']" class="w-4 h-4" />
                <span>{{ $tab['label'] }}</span>
                @if($tab['count'] !== null)
                    <span class="fi-badge rounded-md px-1.5 py-0.5 text-xs font-semibold {{ $activeTab === $tabKey ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                        {{ $tab['count'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    <!-- TAB 1: AGE STAGES & SECTIONS -->
    @if($activeTab === 'age_stages')
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-plus-circle" class="w-5 h-5 text-primary-600" />
                        {{ $editingStageId ? 'تعديل الفئة العمرية' : 'تعريف فئة عمرية وقسم جديد' }}
                    </span>
                </x-slot>

                <form wire:submit="saveStage" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم القسم / المرحلة <span class="text-danger-600">*</span></label>
                            <input type="text" wire:model="stage_name" placeholder="مثال: الحضانة، KG1، KG2..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الحد الأدنى للعمر (بالشهور) <span class="text-danger-600">*</span></label>
                            <input type="number" wire:model="stage_min_age_months" min="0" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الحد الأقصى للعمر (بالشهور) <span class="text-danger-600">*</span></label>
                            <input type="number" wire:model="stage_max_age_months" min="1" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">الوصف والتفاصيل</label>
                            <input type="text" wire:model="stage_description" placeholder="مثال: من 0 إلى 3 سنوات..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">ترتيب العرض</label>
                            <input type="number" wire:model="stage_sort_order" min="1" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="stage_is_active" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 h-4 w-4" />
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">مرحلة مفعّلة في النظام</span>
                        </label>

                        <div class="flex items-center gap-2">
                            @if($editingStageId)
                                <x-filament::button color="gray" size="sm" wire:click="$set('editingStageId', null)">
                                    إلغاء
                                </x-filament::button>
                            @endif
                            <x-filament::button type="submit" size="sm" icon="heroicon-o-check">
                                {{ $editingStageId ? 'حفظ التعديلات' : 'إضافة الفئة العمرية' }}
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            </x-filament::section>

            <!-- Table in Filament Unified Style -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <table class="w-full text-start text-xs divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-gray-950 dark:text-white">
                            <th class="p-3.5 text-start font-bold">الترتيب</th>
                            <th class="p-3.5 text-start font-bold">اسم القسم / المرحلة</th>
                            <th class="p-3.5 text-start font-bold">النطاق العمري</th>
                            <th class="p-3.5 text-start font-bold">الوصف</th>
                            <th class="p-3.5 text-start font-bold">الحالة</th>
                            <th class="p-3.5 text-start font-bold">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($stages as $st)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="p-3.5 font-mono font-bold">{{ $st->sort_order }}</td>
                                <td class="p-3.5 font-bold text-gray-900 dark:text-white text-sm">{{ $st->name }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                                        {{ $st->min_age_months }} - {{ $st->max_age_months }} شهراً
                                    </span>
                                </td>
                                <td class="p-3.5 text-gray-500">{{ $st->description ?? '-' }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $st->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $st->is_active ? 'مفعّل' : 'معطّل' }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="editStage({{ $st->id }})" class="text-primary-600 hover:text-primary-700 font-bold flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-pencil" class="w-3.5 h-3.5" />
                                            <span>تعديل</span>
                                        </button>
                                        <button type="button" wire:click="deleteStage({{ $st->id }})" wire:confirm="هل أنت متأكد من حذف هذه الفئة؟" class="text-rose-600 hover:text-rose-700 font-bold flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-trash" class="w-3.5 h-3.5" />
                                            <span>حذف</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400">لا توجد فئات عمرية مضافة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: ACADEMIC YEARS & TERMS -->
    @if($activeTab === 'academic_calendar')
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-plus-circle" class="w-5 h-5 text-primary-600" />
                        إضافة سنة دراسية جديدة
                    </span>
                </x-slot>

                <form wire:submit="saveYear" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم السنة الدراسية <span class="text-danger-600">*</span></label>
                            <input type="text" wire:model="year_name" placeholder="مثال: 2026-2027" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-bold" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ بداية السنة (ميلادي) <span class="text-danger-600">*</span></label>
                            <input type="date" wire:model="year_start_date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ نهاية السنة (ميلادي) <span class="text-danger-600">*</span></label>
                            <input type="date" wire:model="year_end_date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="year_is_current" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 h-4 w-4" />
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">السنة الحالية النشطة</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit" size="sm" icon="heroicon-o-plus">
                            إضافة السنة الدراسية
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            <!-- List of Years and Terms -->
            <div class="space-y-4">
                @foreach($years as $yr)
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 {{ $yr->is_current ? 'ring-primary-500' : 'ring-gray-950/5 dark:ring-white/10' }} dark:bg-gray-900 p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-3">
                                <span class="text-base font-black text-gray-900 dark:text-white">📅 السنة الدراسية: {{ $yr->name }}</span>
                                @if($yr->is_current)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300">
                                        السنة الحالية النشطة ✅
                                    </span>
                                @else
                                    <button type="button" wire:click="setYearCurrent({{ $yr->id }})" class="text-xs text-primary-600 hover:underline font-bold">
                                        تعيين كسنة حالية
                                    </button>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-xs font-mono text-gray-500">
                                <span>{{ $yr->start_date?->format('Y-m-d') }} ➔ {{ $yr->end_date?->format('Y-m-d') }}</span>
                                <button type="button" wire:click="deleteYear({{ $yr->id }})" wire:confirm="حذف هذه السنة الدراسية وفصولها؟" class="text-rose-600 font-bold hover:underline">حذف</button>
                            </div>
                        </div>

                        <!-- Terms of this year -->
                        <div class="space-y-2">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block">الفصول الدراسية المسجلة:</span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @forelse($yr->terms as $tm)
                                    <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs">
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ $tm->name }}</div>
                                            <div class="text-[11px] font-mono text-primary-600 dark:text-primary-400 mt-0.5">
                                                {{ $tm->start_date?->format('Y-m-d') }} ➔ {{ $tm->end_date?->format('Y-m-d') }}
                                            </div>
                                        </div>
                                        <button type="button" wire:click="deleteTerm({{ $tm->id }})" wire:confirm="حذف هذا الفصل؟" class="text-rose-600 font-bold hover:underline">حذف</button>
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-400 p-2">لا توجد فصول دراسية مضافة لهذه السنة.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Add Term Form -->
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5 text-primary-600" />
                        إضافة فصل دراسي
                    </span>
                </x-slot>

                <form wire:submit="saveTerm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اختر السنة الدراسية <span class="text-danger-600">*</span></label>
                            <select wire:model="term_year_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required>
                                @foreach($years as $yr)
                                    <option value="{{ $yr->id }}">{{ $yr->name }} {{ $yr->is_current ? '(الحالية)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم الفصل الدراسي <span class="text-danger-600">*</span></label>
                            <input type="text" wire:model="term_name" placeholder="الفصل الأول، الفصل الثاني..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ البداية (ميلادي) <span class="text-danger-600">*</span></label>
                            <input type="date" wire:model="term_start_date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ النهاية (ميلادي) <span class="text-danger-600">*</span></label>
                            <input type="date" wire:model="term_end_date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit" size="sm" icon="heroicon-o-plus">
                            إضافة الفصل الدراسي
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>
    @endif

    <!-- TAB 3: HOLIDAYS & VACATIONS -->
    @if($activeTab === 'holidays')
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-sun" class="w-5 h-5 text-primary-600" />
                        إضافة إجازة أو عطلة دراسية رسمية
                    </span>
                </x-slot>

                <form wire:submit="saveHoliday" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم الإجازة / المناسبة <span class="text-danger-600">*</span></label>
                            <input type="text" wire:model="holiday_name" placeholder="مثال: إجازة اليوم الوطني..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ البداية (ميلادي) <span class="text-danger-600">*</span></label>
                            <input type="date" wire:model="holiday_start_date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">تاريخ النهاية (ميلادي) <span class="text-danger-600">*</span></label>
                            <input type="date" wire:model="holiday_end_date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="holiday_affects_subs" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 h-4 w-4" />
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">تمديد الاشتراكات تلقائياً</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit" size="sm" icon="heroicon-o-plus">
                            إضافة الإجازة الدراسية
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            <!-- Holidays List Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <table class="w-full text-start text-xs divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white">
                        <tr>
                            <th class="p-3.5 text-start font-bold">اسم الإجازة</th>
                            <th class="p-3.5 text-start font-bold">تاريخ البداية</th>
                            <th class="p-3.5 text-start font-bold">تاريخ النهاية</th>
                            <th class="p-3.5 text-start font-bold">عدد الأيام</th>
                            <th class="p-3.5 text-start font-bold">أثرها على الاشتراكات</th>
                            <th class="p-3.5 text-start font-bold">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($holidays as $hld)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="p-3.5 font-bold text-gray-900 dark:text-white text-sm">🌴 {{ $hld->name }}</td>
                                <td class="p-3.5 font-mono">{{ $hld->start_date?->format('Y-m-d') }}</td>
                                <td class="p-3.5 font-mono">{{ $hld->end_date?->format('Y-m-d') }}</td>
                                <td class="p-3.5 font-mono font-bold">{{ $hld->days_count }} يوماً</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs {{ $hld->affects_subscriptions ? 'bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300 border border-primary-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $hld->affects_subscriptions ? 'نعم (تمديد)' : 'لا أثر' }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <button type="button" wire:click="deleteHoliday({{ $hld->id }})" wire:confirm="حذف هذه الإجازة؟" class="text-rose-600 font-bold hover:underline flex items-center gap-1">
                                        <x-filament::icon icon="heroicon-o-trash" class="w-3.5 h-3.5" />
                                        <span>حذف</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400">لا توجد إجازات دراسية مضافة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 4: SUBSCRIPTION RULES & ALERTS -->
    @if($activeTab === 'subscription_rules')
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="w-5 h-5 text-primary-600" />
                        قواعد احتساب الاشتراكات والتنبيهات المعتمدة
                    </span>
                </x-slot>

                <form wire:submit="saveRules" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-gray-800">
                            <label class="block text-xs font-bold text-amber-700 dark:text-amber-400 mb-1">
                                ⏳ أيام التنبيه قبل الانتهاء (قرب ينتهي):
                            </label>
                            <input type="number" wire:model="expiring_soon_days" min="1" max="30" class="w-full text-sm font-bold rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                            <span class="text-[11px] text-gray-500 mt-1 block">يتحول الاشتراك للون الأصفر إذا تبقى هذا العدد من الأيام أو أقل</span>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-gray-800">
                            <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-400 mb-1">
                                🎁 نسبة خصم الإخوة المسجلين (%):
                            </label>
                            <input type="number" step="0.5" wire:model="sibling_discount_pct" min="0" max="100" class="w-full text-sm font-bold rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                            <span class="text-[11px] text-gray-500 mt-1 block">تطبق آلياً على الاشتراكات الشهرية والفصول والسنوية</span>
                        </div>

                        <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-gray-800">
                            <label class="block text-xs font-bold text-primary-700 dark:text-primary-400 mb-1">
                                👕 سعر الزي الرسمي المعتمد (ر.س):
                            </label>
                            <input type="number" step="1" wire:model="tshirt_price" min="0" class="w-full text-sm font-bold rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                            <span class="text-[11px] text-gray-500 mt-1 block">تكلفة التيشيرت الرسمي للحضانة</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-filament::button type="submit" icon="heroicon-o-check">
                            حفظ وتحديث قواعد الاشتراكات
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>
    @endif

    <!-- TAB 5: STATUSES & COLORS -->
    @if($activeTab === 'statuses_colors')
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-swatch" class="w-5 h-5 text-primary-600" />
                        دليل ودلالات الحالات والألوان المعتمدة في النظام
                    </span>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full bg-emerald-500 shadow-sm shrink-0"></div>
                        <div>
                            <div class="font-bold text-emerald-900 dark:text-emerald-200 text-sm">ساري 🟢</div>
                            <div class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">اشتراك نشط وساري المفعول ضمن الفترة المحددة</div>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full bg-amber-500 shadow-sm shrink-0"></div>
                        <div>
                            <div class="font-bold text-amber-900 dark:text-amber-200 text-sm">قرب ينتهي 🟡</div>
                            <div class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">متبقي على نهاية الاشتراك {{ $expiring_soon_days }} أيام أو أقل</div>
                        </div>
                    </div>

                    <div class="p-4 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full bg-rose-500 shadow-sm shrink-0"></div>
                        <div>
                            <div class="font-bold text-rose-900 dark:text-rose-200 text-sm">منتهي 🔴</div>
                            <div class="text-xs text-rose-700 dark:text-rose-400 mt-0.5">تجاوز تاريخ نهاية الاشتراك ولم يتم تجديده بعد</div>
                        </div>
                    </div>

                    <div class="p-4 bg-sky-50 dark:bg-sky-950/40 rounded-xl border border-sky-200 dark:border-sky-800 flex items-center gap-3">
                        <div class="w-4 h-4 rounded-full bg-sky-500 shadow-sm shrink-0"></div>
                        <div>
                            <div class="font-bold text-sky-900 dark:text-sky-200 text-sm">حالة السداد 🔵</div>
                            <div class="text-xs text-sky-700 dark:text-sky-400 mt-0.5">عند سداد كامل المبلغ يظهر كلمة "كامل" باللون الأخضر</div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>
    @endif

    <!-- TAB 6: USERS & ROLES -->
    @if($activeTab === 'users_roles')
        <div class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="text-sm font-bold flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-user-plus" class="w-5 h-5 text-primary-600" />
                        إضافة مستخدم جديد لفرع الحضانة
                    </span>
                </x-slot>

                <form wire:submit="saveNewUser" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">اسم الموظف / المستخدم <span class="text-danger-600">*</span></label>
                            <input type="text" wire:model="new_user_name" placeholder="الاسم الكامل..." class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">البريد الإلكتروني <span class="text-danger-600">*</span></label>
                            <input type="email" wire:model="new_user_email" placeholder="user@nursery.com" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">كلمة المرور <span class="text-danger-600">*</span></label>
                            <input type="password" wire:model="new_user_password" placeholder="6 خانات على الأقل" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 p-2 font-mono" required />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit" size="sm" icon="heroicon-o-plus">
                            إنشاء المستخدم
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>

            <!-- Users List Table in Unified Style -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <table class="w-full text-start text-xs divide-y divide-gray-200 dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white">
                        <tr>
                            <th class="p-3.5 text-start font-bold">اسم المستخدم</th>
                            <th class="p-3.5 text-start font-bold">البريد الإلكتروني</th>
                            <th class="p-3.5 text-start font-bold">الدور / الصلاحيات</th>
                            <th class="p-3.5 text-start font-bold">تاريخ الإنشاء</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach($users as $usr)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="p-3.5 font-bold text-gray-900 dark:text-white text-sm">👤 {{ $usr->name }}</td>
                                <td class="p-3.5 font-mono text-gray-600 dark:text-gray-400">{{ $usr->email }}</td>
                                <td class="p-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300">
                                        {{ $usr->roles->pluck('name')->implode(', ') ?: 'مدير فرع' }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-mono text-gray-500">{{ $usr->created_at?->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-filament-panels::page>
