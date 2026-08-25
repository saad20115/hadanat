<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <span class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-6 h-6 text-primary-600" />
                    ملخص اشتراكات الأطفال حسب الأقسام وفئات الأعمار
                </span>
                <div class="flex items-center gap-2">
                    <a href="/admin/nursery/settings/age-stages" class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-600 transition">
                        <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-4 h-4 text-gray-500" />
                        تعديل إعدادات فئات الأعمار
                    </a>
                    <span class="text-xs font-semibold px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full">
                        مرتبط آلياً بإعدادات الفئات
                    </span>
                </div>
            </div>
        </x-slot>

        <!-- Summary Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 my-2">
            <table class="w-full text-sm text-center border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold border-b border-gray-200 dark:border-gray-700">
                        <th class="p-3 text-right">القسم / الفئة العمرية المعرفة</th>
                        <th class="p-3">نطاق العمر المعتمد</th>
                        <th class="p-3">عدد الأطفال</th>
                        <th class="p-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                ساري
                            </span>
                        </th>
                        <th class="p-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                قرب ينتهي
                            </span>
                        </th>
                        <th class="p-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                منتهي
                            </span>
                        </th>
                        <th class="p-3">إجمالي الاشتراكات</th>
                        <th class="p-3">إجمالي المتبقي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    @foreach ($rows as $row)
                        <tr class="{{ $row['is_total'] ? 'bg-primary-50/50 dark:bg-gray-800/80 font-bold text-base border-t-2 border-primary-300 dark:border-gray-600' : 'hover:bg-gray-50/50 dark:hover:bg-gray-800/40' }}">
                            <td class="p-3 text-right">
                                <div class="{{ $row['is_total'] ? 'text-primary-700 dark:text-primary-400 font-extrabold text-base' : 'font-bold text-gray-900 dark:text-white' }}">
                                    {{ $row['section'] }}
                                </div>
                                @if(!empty($row['description']) && !$row['is_total'])
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 font-normal">
                                        {{ $row['description'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="p-3 font-normal text-xs text-gray-600 dark:text-gray-300">
                                <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                    {{ $row['age_bracket'] }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-gray-800 dark:text-gray-200">
                                {{ $row['children_count'] }}
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-md text-xs font-bold {{ $row['active_count'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800' : 'text-gray-400' }}">
                                    {{ $row['active_count'] }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-md text-xs font-bold {{ $row['expiring_soon_count'] > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800' : 'text-gray-400' }}">
                                    {{ $row['expiring_soon_count'] }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-md text-xs font-bold {{ $row['expired_count'] > 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800' : 'text-gray-400' }}">
                                    {{ $row['expired_count'] }}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-gray-900 dark:text-white">
                                {{ number_format($row['total_amount'], 2) }} <span class="text-xs text-gray-500 font-normal">ر.س</span>
                            </td>
                            <td class="p-3 font-bold {{ $row['remaining_amount'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                @if($row['remaining_amount'] > 0)
                                    {{ number_format($row['remaining_amount'], 2) }} <span class="text-xs font-normal">ر.س</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                        كامل
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
