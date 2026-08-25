<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <span class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-adjustments-horizontal" class="w-5 h-5 text-primary-600" />
                    إعدادات القواعد والتواريخ الأكاديمية ودليل الحالات (قابلة للتعديل والتحكم)
                </span>
                <button type="button" wire:click="saveSettings" class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-lg shadow transition">
                    <x-filament::icon icon="heroicon-o-check" class="w-4 h-4" />
                    حفظ وتحديث القواعد
                </button>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Editable Academic Dates & Parameters (7 Cols) -->
            <div class="lg:col-span-7 bg-gray-50 dark:bg-gray-800/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-4">
                <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5 text-sm pb-2 border-b border-gray-200 dark:border-gray-700">
                    <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4 text-primary-500" />
                    المواعيد والقواعد الأكاديمية الرسمية (ميلادية)
                </h4>

                <!-- Term 1 -->
                <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">
                        🎓 الفصل الدراسي الأول (يوم/شهر):
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-0.5">تاريخ البداية (MM-DD):</label>
                            <input type="text" wire:model="term1_start" placeholder="08-30" class="w-full text-xs font-mono font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5" />
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-0.5">تاريخ النهاية (MM-DD):</label>
                            <input type="text" wire:model="term1_end" placeholder="01-07" class="w-full text-xs font-mono font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Term 2 -->
                <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">
                        🎓 الفصل الدراسي الثاني (يوم/شهر):
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-0.5">تاريخ البداية (MM-DD):</label>
                            <input type="text" wire:model="term2_start" placeholder="01-17" class="w-full text-xs font-mono font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5" />
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-0.5">تاريخ النهاية (MM-DD):</label>
                            <input type="text" wire:model="term2_end" placeholder="07-01" class="w-full text-xs font-mono font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Full Year -->
                <div class="bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">
                        🏫 السنة الدراسية الكاملة:
                    </span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-0.5">تاريخ البداية (MM-DD):</label>
                            <input type="text" wire:model="yearly_start" placeholder="08-30" class="w-full text-xs font-mono font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5" />
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-500 block mb-0.5">تاريخ النهاية من العام التالي:</label>
                            <input type="text" wire:model="yearly_end" placeholder="07-01" class="w-full text-xs font-mono font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5" />
                        </div>
                    </div>
                </div>

                <!-- Rules & Discounts Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                    <div class="bg-white dark:bg-gray-900 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                        <label class="text-[11px] font-bold text-amber-700 dark:text-amber-400 block mb-1">
                            ⏳ أيام "قرب ينتهي":
                        </label>
                        <input type="number" wire:model="expiring_soon_days" class="w-full text-xs font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5 font-mono" />
                    </div>

                    <div class="bg-white dark:bg-gray-900 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                        <label class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 block mb-1">
                            🎁 نسبة خصم الإخوة (%):
                        </label>
                        <input type="number" step="0.5" wire:model="sibling_discount_pct" class="w-full text-xs font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5 font-mono" />
                    </div>

                    <div class="bg-white dark:bg-gray-900 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700">
                        <label class="text-[11px] font-bold text-primary-700 dark:text-primary-400 block mb-1">
                            👕 سعر الزي الرسمي (ر.س):
                        </label>
                        <input type="number" step="1" wire:model="tshirt_price" class="w-full text-xs font-bold rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white p-1.5 font-mono" />
                    </div>
                </div>
            </div>

            <!-- Right: Color Guide & Status Legend (5 Cols) -->
            <div class="lg:col-span-5 bg-gray-50 dark:bg-gray-800/60 p-4 rounded-xl border border-gray-200 dark:border-gray-700 space-y-3">
                <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5 text-sm pb-2 border-b border-gray-200 dark:border-gray-700">
                    <x-filament::icon icon="heroicon-o-swatch" class="w-4 h-4 text-primary-500" />
                    دليل الألوان وحالات الاشتراكات
                </h4>

                <div class="space-y-2 text-xs">
                    <!-- Active -->
                    <div class="flex items-center justify-between p-2.5 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg border border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-emerald-500 shadow-sm shrink-0"></div>
                            <div>
                                <div class="font-bold text-emerald-900 dark:text-emerald-200">ساري 🟢</div>
                                <div class="text-[10px] text-emerald-700 dark:text-emerald-400">اشتراك نشط وساري المفعول</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono bg-white dark:bg-gray-900 text-emerald-800 dark:text-emerald-300 px-2 py-0.5 rounded border border-emerald-300">
                            ساري
                        </span>
                    </div>

                    <!-- Expiring Soon -->
                    <div class="flex items-center justify-between p-2.5 bg-amber-50 dark:bg-amber-950/40 rounded-lg border border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-amber-500 shadow-sm shrink-0"></div>
                            <div>
                                <div class="font-bold text-amber-900 dark:text-amber-200">قرب ينتهي 🟡</div>
                                <div class="text-[10px] text-amber-700 dark:text-amber-400">متبقي {{ $expiring_soon_days }} أيام أو أقل</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono bg-white dark:bg-gray-900 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded border border-amber-300">
                            قرب ينتهي
                        </span>
                    </div>

                    <!-- Expired -->
                    <div class="flex items-center justify-between p-2.5 bg-rose-50 dark:bg-rose-950/40 rounded-lg border border-rose-200 dark:border-rose-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-rose-500 shadow-sm shrink-0"></div>
                            <div>
                                <div class="font-bold text-rose-900 dark:text-rose-200">منتهي 🔴</div>
                                <div class="text-[10px] text-rose-700 dark:text-rose-400">بعد تاريخ نهاية الاشتراك</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono bg-white dark:bg-gray-900 text-rose-800 dark:text-rose-300 px-2 py-0.5 rounded border border-rose-300">
                            منتهي
                        </span>
                    </div>

                    <!-- Full Paid / Balance -->
                    <div class="flex items-center justify-between p-2.5 bg-sky-50 dark:bg-sky-950/40 rounded-lg border border-sky-200 dark:border-sky-800">
                        <div class="flex items-center gap-2.5">
                            <div class="w-3.5 h-3.5 rounded-full bg-sky-500 shadow-sm shrink-0"></div>
                            <div>
                                <div class="font-bold text-sky-900 dark:text-sky-200">حالة السداد 🔵</div>
                                <div class="text-[10px] text-sky-700 dark:text-sky-400">المبلغ المسدد أو كلمة "كامل"</div>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 px-2 py-0.5 rounded">
                            {{ $paid_status_label }}
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-200 dark:border-gray-700 text-center">
                    <button type="button" wire:click="saveSettings" class="w-full py-2 px-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-bold text-xs shadow transition flex items-center justify-center gap-1.5">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4" />
                        حفظ وتطبيق القواعد
                    </button>
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
