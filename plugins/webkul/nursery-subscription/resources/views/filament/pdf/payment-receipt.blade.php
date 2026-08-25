<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سند قبض - {{ $payment->receipt_number }}</title>
    <style>
        body {
            font-family: 'XBRiyaz', 'Cairo', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #fff;
        }
        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #005f73;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #005f73;
            margin: 0 0 10px 0;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .receipt-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .receipt-info-row {
            display: table-row;
        }
        .receipt-info-cell {
            display: table-cell;
            padding: 5px;
            width: 50%;
        }
        .section-title {
            background-color: #f4f4f4;
            padding: 10px;
            font-weight: bold;
            border-right: 4px solid #005f73;
            margin-bottom: 15px;
            margin-top: 20px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: right;
        }
        .details-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .totals-table {
            width: 50%;
            margin-right: auto;
            margin-left: 0;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .totals-table .total-row td {
            font-weight: bold;
            border-bottom: 2px solid #333;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dotted #ccc;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>{{ config('school.company_name', 'مدرسة العقول النامية الأهلية') }}</h1>
            <p style="font-size: 13px; color: #555; margin: 4px 0;">
                الرقم الضريبي: <strong>{{ config('school.vat_number', '310623259700003') }}</strong> | 
                السجل التجاري: <strong>{{ config('school.cr_number', '7015447258') }}</strong>
            </p>
            <p style="font-size: 12px; color: #777; margin: 2px 0;">{{ config('school.address', 'جدة - المملكة العربية السعودية') }}</p>
            <h2 style="font-size: 18px; color: #005f73; margin-top: 10px; margin-bottom: 0;">سند قبض / إيصال سداد رسوم</h2>
        </div>
        
        <div class="receipt-info">
            <div class="receipt-info-row">
                <div class="receipt-info-cell">
                    <strong>رقم الإيصال:</strong> {{ $payment->receipt_number }}
                </div>
                <div class="receipt-info-cell">
                    <strong>التاريخ:</strong> {{ $payment->payment_date->format('Y-m-d') }}
                </div>
            </div>
            <div class="receipt-info-row">
                <div class="receipt-info-cell">
                    <strong>طريقة الدفع:</strong> {{ __('nursery-subscription::filament/nursery.enums.payment_method.' . $payment->payment_method) }}
                </div>
                <div class="receipt-info-cell">
                    <strong>رقم المرجع:</strong> {{ $payment->reference_number ?: '---' }}
                </div>
            </div>
        </div>
        
        <div class="section-title">بيانات المشترك</div>
        <table class="details-table">
            <tr>
                <th>اسم الطفل</th>
                <td>{{ $payment->subscription->child->full_name }}</td>
                <th>ولي الأمر</th>
                <td>{{ $payment->subscription->child->guardian_name }}</td>
            </tr>
            <tr>
                <th>الهاتف</th>
                <td>{{ $payment->subscription->child->guardian_phone }}</td>
                <th>الباقة</th>
                <td>{{ $payment->subscription->pricingPlan->name }}</td>
            </tr>
            <tr>
                <th>فترة الاشتراك</th>
                <td colspan="3">من {{ $payment->subscription->start_date->format('Y-m-d') }} إلى {{ $payment->subscription->end_date->format('Y-m-d') }}</td>
            </tr>
        </table>
        
        <div class="section-title">تفاصيل الدفعة</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th>البيان</th>
                    <th>المبلغ (ر.س)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>دفعة اشتراك حضانة - {{ $payment->subscription->pricingPlan->name }}</td>
                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        <table class="totals-table">
            <tr>
                <td>إجمالي قيمة الاشتراك:</td>
                <td>{{ number_format((float) $payment->subscription->net_amount, 2) }} ر.س</td>
            </tr>
            <tr>
                <td>المدفوع مسبقاً:</td>
                <td>{{ number_format((float) ($payment->subscription->paid_amount - $payment->amount), 2) }} ر.س</td>
            </tr>
            <tr class="total-row">
                <td>قيمة الدفعة الحالية:</td>
                <td>{{ number_format((float) $payment->amount, 2) }} ر.س</td>
            </tr>
            <tr>
                <td>المتبقي على الاشتراك:</td>
                <td>{{ number_format((float) $payment->subscription->remaining_amount, 2) }} ر.س</td>
            </tr>
        </table>
        
        <div class="footer">
            <p>نشكركم لثقتكم بنا. الأسعار تشمل ضريبة القيمة المضافة (15%).</p>
        </div>
    </div>
</body>
</html>
