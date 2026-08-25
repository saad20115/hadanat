<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Webkul\NurserySubscription\Enums\SubscriptionStatus;
use Webkul\NurserySubscription\Models\Subscription;

class UpdateSubscriptionStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nursery:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update nursery subscription statuses based on dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today()->format('Y-m-d');

        $this->info("Updating subscription statuses for {$today}...");

        $newToActive = 0;
        $activeToExpiring = 0;
        $expiringToExpired = 0;
        $activeToExpired = 0;

        // NEW -> ACTIVE
        Subscription::where('status', SubscriptionStatus::NEW->value)
            ->whereDate('start_date', '<=', $today)
            ->chunkById(100, function ($subscriptions) use (&$newToActive) {
                foreach ($subscriptions as $subscription) {
                    $subscription->status = SubscriptionStatus::ACTIVE;
                    $subscription->save();
                    $newToActive++;
                }
            });

        $sevenDaysLater = Carbon::today()->addDays(7)->format('Y-m-d');

        // ACTIVE -> EXPIRING_SOON
        Subscription::where('status', SubscriptionStatus::ACTIVE->value)
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $sevenDaysLater)
            ->chunkById(100, function ($subscriptions) use (&$activeToExpiring) {
                foreach ($subscriptions as $subscription) {
                    $subscription->status = SubscriptionStatus::EXPIRING_SOON;
                    $subscription->save();
                    $activeToExpiring++;
                }
            });

        // EXPIRING_SOON -> EXPIRED
        Subscription::where('status', SubscriptionStatus::EXPIRING_SOON->value)
            ->whereDate('end_date', '<', $today)
            ->chunkById(100, function ($subscriptions) use (&$expiringToExpired) {
                foreach ($subscriptions as $subscription) {
                    $subscription->status = SubscriptionStatus::EXPIRED;
                    $subscription->save();
                    $expiringToExpired++;
                }
            });

        // ACTIVE -> EXPIRED (Direct fallback)
        Subscription::where('status', SubscriptionStatus::ACTIVE->value)
            ->whereDate('end_date', '<', $today)
            ->chunkById(100, function ($subscriptions) use (&$activeToExpired) {
                foreach ($subscriptions as $subscription) {
                    $subscription->status = SubscriptionStatus::EXPIRED;
                    $subscription->save();
                    $activeToExpired++;
                }
            });

        $this->info('Status update complete.');
        $this->table(
            ['Transition', 'Count'],
            [
                ['NEW -> ACTIVE', $newToActive],
                ['ACTIVE -> EXPIRING_SOON', $activeToExpiring],
                ['EXPIRING_SOON -> EXPIRED', $expiringToExpired],
                ['ACTIVE -> EXPIRED', $activeToExpired],
            ]
        );

        return self::SUCCESS;
    }
}
