---
name: nursery-subscription-system
description: Comprehensive knowledge base, architectural guidelines, business rules, pricing matrices, and technical patterns for the Nursery and Kindergarten Management System in Aureus ERP.
---

# دليل ومهارات تطوير نظام إدارة واشتراكات الحضانة ورياض الأطفال
## (Nursery & Kindergarten Subscription System Knowledge & Skills)

تم توثيق كافة الخبرات، الأنماط البرمجية، القواعد المحاسبية، والتقويم الأكاديمي المكتسبة أثناء تطوير نظام الحضانة على منصة **Aureus ERP** و **Filament 5**.

---

## 🏗️ 1. المعمارية البرمجية والمكونات (Architecture & Structure)

- **مسار الإضافة (Plugin Path):** `plugins/webkul/nursery-subscription/`
- **مقدم الخدمة (Service Provider):** `Webkul\NurserySubscription\NurserySubscriptionServiceProvider`
- **إضافة فيلامينت (Filament Plugin):** `Webkul\NurserySubscription\NurserySubscriptionPlugin`

### 🗂️ تنظيم القوائم ولوحة التحكم:
1. **المجموعة الرئيسية (NavigationGroup::Nursery - إدارة الحضانة):**
   - `SubscriptionResource`: إدارة الاشتراكات وحالاتها والتجديد والمدفوعات.
   - `ChildResource`: تسجيل الأطفال، أولياء الأمور، واحتساب الفئات العمرية تلقائياً بالشهور.
   - `SubscriptionCalculator`: حاسبة تسعير فورية ومبسطة بالتواريخ الميلادية الرسمية.
   - `PricingPlanResource`: مصفوفة الباقات والأسعار (ساعات، أيام، أسابيع، أشهر، فصول، سنوي، زيارات).
   - `NurseryReports`: لوحة تقارير ومؤشرات أداء متقدمة وإيرادات ونسب إشغال.
   - `PaymentResource`: سندات التحصيل والمدفوعات.

2. **قائمة الإعدادات المدمجة (Configurations Cluster - الإعدادات):**
   - `NurseryCompanyResource`: بيانات الحضانة، الرقم الضريبي، السجل، وأرقام التواصل.
   - `AgeStageRuleResource`: فئات الأعمار والأقسام (الحضانة، KG1، KG2، KG3).
   - `AcademicYearResource`: إدارة السنوات والفصول الدراسية وتحديد السنة الحالية.
   - `HolidayResource`: الإجازات والعطل المدرسية وخيار تمديد الاشتراكات تلقائياً.
   - `NurseryUserResource`: إدارة مستخدمي الحضانة وتنشيط/تعطيل الحسابات بضغطة زر.

---

## 📅 2. التقويم الأكاديمي المعتمد (Academic Calendar - Gregorian)

- **الفصل الدراسي الأول:** من `30/08` إلى `07/01` (من العام التالي).
- **الفصل الدراسي الثاني:** من `17/01` إلى `01/07`.
- **السنة الدراسية الكاملة:** من `30/08` إلى `01/07` من العام التالي.
- **تنبيه "قرب ينتهي":** عندما يتبقى `7 أيام أو أقل` على نهاية الاشتراك.
- **الاشتراك المنتهي:** بعد تجاوز تاريخ نهاية الاشتراك.
- **حالة السداد:** إذا كان المتبقي `0.00 ر.س`، يظهر بادج أخضر مكتوب عليه كلمة **`كامل`**.

---

## 💰 3. القواعد المالية والتسعير والخصومات

1. **نسبة خصم الإخوة:** `5%` تطبق تلقائياً على الاشتراكات الشهرية والفصول والسنوية للأطفال الذين لديهم إخوة مسجلين.
2. **الزي الرسمي (التيشيرت):** `75.00 ر.س` يضاف اختيارياً عند التسجيل.
3. **ضريبة القيمة المضافة (VAT):** كافة الأسعار في الباقات تعتبر شاملة لضريبة القيمة المضافة بنسبة `15%`.
4. **خدمة احتساب التواريخ والأسعار:** الفئة `Webkul\NurserySubscription\Services\PricingAndLifecycleService` هي المسؤولة عن احتساب تواريخ الانتهاء، الصافي، المدفوع، والمتبقي.

---

## 🔒 4. الأمان والصلاحيات (Security & Scoping)

1. **عزل الشركات والفروع (Multi-Tenancy):**
   - استخدام التريت `BelongsToCompany` لضمان أن كل مدرسة أو فرع يرى فقط سجلاته وبياناته (`company_id`).
2. **حجب إعدادات النظام العامة عن غير السوبر أدمن:**
   - تم قفل وعزل قائمة `NavigationGroup::Setting` لتظهر فقط للمستخدمين برتبة `Super_admin`.
   - الموظفون العاديون ومديرو الفروع يرون فقط القوائم المخصصة للحضانة وإعداداتها الفرعية.

---

## 🛠️ 5. أوامر الصيانة والتشغيل

- **تحديث حالات الاشتراكات آلياً:**
  ```bash
  php artisan nursery:update-statuses
  ```
- **تشغيل خادم التطوير:**
  ```bash
  php artisan serve --host=127.0.0.1 --port=8080
  ```
- **مسح الكاش:**
  ```bash
  php artisan optimize:clear
  ```
