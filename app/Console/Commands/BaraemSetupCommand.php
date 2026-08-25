<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BaraemSetupCommand extends Command
{
    protected $signature = 'baraem:setup {--force : Force execution}';

    protected $description = 'Complete initialization and setup for Baraem ERP system';

    public function handle(): int
    {
        $this->info('🚀 Starting Baraem ERP Setup...');

        // 1. Run core migrations
        $this->info('1. Running Core Migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        // 2. Seed Currencies
        $this->info('2. Seeding Currencies...');
        Artisan::call('db:seed', [
            '--class' => 'Webkul\\Support\\Database\\Seeders\\CurrencySeeder',
            '--force' => true,
        ]);

        // 3. Ensure Partner 1 and Company 1 exist
        $this->info('3. Ensuring Company 1 and SAR Currency...');
        $sarId = DB::table('currencies')->where('name', 'SAR')->value('id') ?? 150;
        DB::table('currencies')->where('id', $sarId)->update(['active' => true, 'symbol' => 'ر.س']);

        if (! DB::table('partners_partners')->where('id', 1)->exists()) {
            DB::table('partners_partners')->insert([
                'id'         => 1,
                'name'       => 'شركة براعم لخدمات رياض الأطفال والحضانات',
                'email'      => 'info@hadanat.com',
                'is_company' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('companies')->where('id', 1)->exists()) {
            DB::table('companies')->insert([
                'id'          => 1,
                'name'        => 'شركة براعم لخدمات رياض الأطفال والحضانات',
                'partner_id'  => 1,
                'currency_id' => $sarId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 4. Run Plugin Migrations
        $this->info('4. Running Plugin Migrations...');
        $allMigrationFiles = [];
        $dirIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('plugins/webkul')));
        foreach ($dirIterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                $cleanPath = str_replace('\\', '/', $file->getPathname());
                if (str_contains($cleanPath, 'database/migrations')) {
                    $allMigrationFiles[] = $cleanPath;
                }
            }
        }

        usort($allMigrationFiles, function ($a, $b) {
            return strcmp(basename($a), basename($b));
        });

        foreach ($allMigrationFiles as $filePath) {
            $migrationName = pathinfo($filePath, PATHINFO_FILENAME);
            $alreadyRun = DB::table('migrations')->where('migration', $migrationName)->exists();

            if (! $alreadyRun) {
                try {
                    $migrationObj = require $filePath;
                    if (is_object($migrationObj) && method_exists($migrationObj, 'up')) {
                        $migrationObj->up();
                        DB::table('migrations')->insert([
                            'migration' => $migrationName,
                            'batch'     => 10,
                        ]);
                    }
                } catch (\Throwable $e) {
                    if (str_contains($e->getMessage(), 'already exists')) {
                        DB::table('migrations')->insertOrIgnore([
                            'migration' => $migrationName,
                            'batch'     => 10,
                        ]);
                    }
                }
            }
        }

        // 5. Seed Plugins table
        $this->info('5. Initializing Plugins Registry...');
        Artisan::call('db:seed', [
            '--class' => 'Webkul\\PluginManager\\Database\\Seeders\\PluginSeeder',
            '--force' => true,
        ]);

        // 6. Seed Users
        $this->info('6. Seeding Admin & Role Users...');
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\AdminUserSeeder',
            '--force' => true,
        ]);
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\DemoUsersSeeder',
            '--force' => true,
        ]);

        // 7. Seed Nursery Subscriptions
        $this->info('7. Seeding Baraem Plans & Academic Calendar...');
        Artisan::call('db:seed', [
            '--class' => 'Webkul\\NurserySubscription\\Database\\Seeders\\NurserySubscriptionDatabaseSeeder',
            '--force' => true,
        ]);

        // 8. Storage link & optimize
        $this->info('8. Linking Storage & Optimizing Cache...');
        Artisan::call('storage:link');
        Artisan::call('optimize:clear');

        $this->info('🎉 Baraem ERP Setup Completed Successfully!');

        return Command::SUCCESS;
    }
}