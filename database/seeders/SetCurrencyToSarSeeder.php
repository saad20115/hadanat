<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Webkul\Support\Models\Company;
use Webkul\Support\Models\Currency;

class SetCurrencyToSarSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Find or create Saudi Riyal (SAR / ر.س)
        $sar = Currency::where('code', 'SAR')
            ->orWhere('name', 'SAR')
            ->orWhere('symbol', 'ر.س')
            ->orWhere('symbol', 'SAR')
            ->first();

        if (! $sar) {
            $sar = Currency::create([
                'name'             => 'SAR',
                'code'             => 'SAR',
                'symbol'           => 'ر.س',
                'currency_unit'    => 'ريال',
                'currency_subunit' => 'هللة',
                'decimal_places'   => 2,
                'active'           => true,
            ]);
        } else {
            $sar->active = true;
            $sar->symbol = 'ر.س';
            $sar->currency_unit = 'ريال';
            $sar->currency_subunit = 'هللة';
            $sar->save();
        }

        // 2. Set company currency to SAR
        if (Schema::hasTable('companies')) {
            DB::table('companies')->update(['currency_id' => $sar->id]);
        }

        // 3. Set all accounts currency to SAR
        if (Schema::hasTable('accounts_accounts')) {
            DB::table('accounts_accounts')->update(['currency_id' => $sar->id]);
        }

        // 4. Set all journals currency to SAR
        if (Schema::hasTable('accounts_journals')) {
            DB::table('accounts_journals')->update(['currency_id' => $sar->id]);
        }

        // 5. Inactivate USD or non-SAR default currencies if any
        Currency::where('id', '!=', $sar->id)->update(['active' => false]);
    }
}
