<?php

declare(strict_types=1);

namespace Webkul\NurserySubscription\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\NurserySubscription\Enums\AgeStage;
use Webkul\NurserySubscription\Enums\DurationType;
use Webkul\NurserySubscription\Enums\PaymentMethod;
use Webkul\NurserySubscription\Enums\SubscriptionStatus;
use Webkul\NurserySubscription\Models\Child;
use Webkul\NurserySubscription\Models\Payment;
use Webkul\NurserySubscription\Models\PricingPlan;
use Webkul\NurserySubscription\Models\Subscription;

class PricingAndLifecycleService
{
    /**
     * Calculate the end date for a subscription plan based on the start date.
     *
     * @param PricingPlan $plan
     * @param Carbon $startDate
     * @return Carbon
     */
    public function calculateEndDate(PricingPlan $plan, Carbon $startDate): Carbon
    {
        $endDate = clone $startDate;

        switch ($plan->duration_type) {
            case DurationType::HOURLY:
            case DurationType::DAILY:
                // End date is the same day as start date
                break;
            case DurationType::WEEKLY:
                $weeks = (int) ($plan->duration_value ?: 1);
                $endDate->addDays($weeks * 7 - 1);
                break;
            case DurationType::MONTHLY:
                $months = (int) ($plan->duration_value ?: 1);
                $endDate->addMonths($months)->subDay();
                break;
            case DurationType::TERM:
                $value = (float) $plan->duration_value;
                $startMonth = (int) $startDate->format('n');
                $startDay = (int) $startDate->format('j');
                $year = (int) $startDate->format('Y');

                // Term 1 (30/08 to 07/01 next year)
                if (str_contains($plan->duration_label, 'الأول') || $value == 4.25 || ($startMonth >= 8 && $startMonth <= 10)) {
                    $endYear = ($startMonth >= 8) ? $year + 1 : $year;
                    return Carbon::create($endYear, 1, 7, 0, 0, 0);
                }

                // Term 2 (17/01 to 01/07)
                if (str_contains($plan->duration_label, 'الثاني') || $value == 5.5 || ($startMonth >= 1 && $startMonth <= 3)) {
                    return Carbon::create($year, 7, 1, 0, 0, 0);
                }

                $endDate->addMonths((int) ceil($value))->subDay();
                break;
            case DurationType::YEARLY:
                $startMonth = (int) $startDate->format('n');
                $year = (int) $startDate->format('Y');
                // Academic Full Year (30/08 to 01/07 next year)
                if ($startMonth >= 7) {
                    return Carbon::create($year + 1, 7, 1, 0, 0, 0);
                }
                return Carbon::create($year, 7, 1, 0, 0, 0);
            case DurationType::VISIT_PACKAGE:
                $months = (int) ($plan->visits_period_months ?: 1);
                if ($months > 0) {
                    $endDate->addMonths($months)->subDay();
                }
                break;
        }

        return $endDate;
    }

    /**
     * Calculate net amounts and discounts for a subscription.
     *
     * @param PricingPlan $plan
     * @param Child $child
     * @param bool $includeTshirt
     * @return array
     */
    public function calculateNetAmount(PricingPlan $plan, Child $child, bool $includeTshirt = false): array
    {
        $basePrice = (float) $plan->price;
        $siblingDiscountPct = 0.00;
        $discountAmount = 0.00;

        $eligibleDurationTypes = [
            DurationType::MONTHLY,
            DurationType::TERM,
            DurationType::YEARLY,
        ];

        if (in_array($plan->duration_type, $eligibleDurationTypes, true) && $child->has_siblings) {
            if ($this->determineSiblingEligibility($child)) {
                $siblingDiscountPct = 5.00;
                $discountAmount = $basePrice * 0.05;
            }
        }

        $tshirtAmount = $includeTshirt ? 75.00 : 0.00;
        $netAmount = $basePrice - $discountAmount + $tshirtAmount;

        return [
            'base_price' => round($basePrice, 2),
            'sibling_discount_pct' => round($siblingDiscountPct, 2),
            'discount_amount' => round($discountAmount, 2),
            'tshirt_amount' => round($tshirtAmount, 2),
            'net_amount' => round($netAmount, 2),
        ];
    }

    /**
     * Determine if a child is eligible for a sibling discount.
     *
     * @param Child $child
     * @return bool
     */
    public function determineSiblingEligibility(Child $child): bool
    {
        if (empty($child->guardian_phone)) {
            return false;
        }

        return Child::where('company_id', $child->company_id)
            ->where('guardian_phone', $child->guardian_phone)
            ->where('id', '!=', $child->id)
            ->whereHas('subscriptions', function ($query) {
                $query->whereIn('status', [
                    SubscriptionStatus::ACTIVE->value,
                    SubscriptionStatus::EXPIRING_SOON->value,
                    SubscriptionStatus::NEW->value,
                ]);
            })
            ->exists();
    }

    /**
     * Determine the correct status for a given subscription based on dates or relations.
     *
     * @param Subscription $subscription
     * @return SubscriptionStatus
     */
    public function determineStatus(Subscription $subscription): SubscriptionStatus
    {
        if ($subscription->renewedBy()->exists()) {
            return SubscriptionStatus::RENEWED;
        }

        if (in_array($subscription->status, [SubscriptionStatus::CANCELLED, SubscriptionStatus::FROZEN], true)) {
            return $subscription->status;
        }

        $today = Carbon::today();

        if ($subscription->end_date->lt($today)) {
            return SubscriptionStatus::EXPIRED;
        }

        if ($subscription->start_date->gt($today)) {
            return SubscriptionStatus::NEW;
        }

        $daysUntilEnd = $today->diffInDays($subscription->end_date, false);
        if ($daysUntilEnd >= 0 && $daysUntilEnd <= 7) {
            return SubscriptionStatus::EXPIRING_SOON;
        }

        return SubscriptionStatus::ACTIVE;
    }

    /**
     * Create a new subscription.
     *
     * @param Child $child
     * @param PricingPlan $plan
     * @param Carbon $startDate
     * @param bool $includeTshirt
     * @param float|null $initialPayment
     * @param string|null $paymentMethod
     * @param int|null $renewalOfId
     * @return Subscription
     */
    public function createSubscription(
        Child $child,
        PricingPlan $plan,
        Carbon $startDate,
        bool $includeTshirt = false,
        ?float $initialPayment = null,
        string|PaymentMethod|null $paymentMethod = null,
        ?int $renewalOfId = null
    ): Subscription {
        return DB::transaction(function () use (
            $child,
            $plan,
            $startDate,
            $includeTshirt,
            $initialPayment,
            $paymentMethod,
            $renewalOfId
        ) {
            $endDate = $this->calculateEndDate($plan, clone $startDate);
            $pricing = $this->calculateNetAmount($plan, $child, $includeTshirt);

            $today = Carbon::today();
            $status = SubscriptionStatus::ACTIVE;
            if ($startDate->gt($today)) {
                $status = SubscriptionStatus::NEW;
            } elseif ($today->diffInDays($endDate, false) >= 0 && $today->diffInDays($endDate, false) <= 7) {
                $status = SubscriptionStatus::EXPIRING_SOON;
            } elseif ($endDate->lt($today)) {
                $status = SubscriptionStatus::EXPIRED;
            }

            $subscription = new Subscription([
                'company_id' => $child->company_id,
                'child_id' => $child->id,
                'pricing_plan_id' => $plan->id,
                'status' => $status,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                
                'base_price' => $pricing['base_price'],
                'sibling_discount_pct' => $pricing['sibling_discount_pct'],
                'discount_amount' => $pricing['discount_amount'],
                'tshirt_amount' => $pricing['tshirt_amount'],
                'net_amount' => $pricing['net_amount'],
                
                'paid_amount' => 0.00,
                'remaining_amount' => $pricing['net_amount'],
                'includes_tshirt' => $includeTshirt,
            ]);
            
            $subscription->save();

            if ($renewalOfId) {
                $oldSub = Subscription::find($renewalOfId);
                if ($oldSub) {
                    $oldSub->status = SubscriptionStatus::RENEWED;
                    $oldSub->save();
                    
                    $subscription->renewal_of_id = $renewalOfId;
                    $subscription->save();
                }
            }

            if ($initialPayment !== null && $initialPayment > 0 && $paymentMethod !== null) {
                $this->recordPayment($subscription, $initialPayment, $paymentMethod, Carbon::now());
            }

            return $subscription->refresh();
        });
    }

    /**
     * Process the renewal of an existing subscription.
     *
     * @param Subscription $currentSubscription
     * @return Subscription
     */
    public function processRenewal(Subscription $currentSubscription): Subscription
    {
        return DB::transaction(function () use ($currentSubscription) {
            $startDate = clone $currentSubscription->end_date;
            $startDate->addDay();
            
            $plan = $currentSubscription->pricingPlan;
            $child = $currentSubscription->child;
            $includeTshirt = (bool) $currentSubscription->includes_tshirt;

            return $this->createSubscription(
                $child,
                $plan,
                $startDate,
                $includeTshirt,
                null,
                null,
                $currentSubscription->id
            );
        });
    }

    /**
     * Record a payment against a subscription.
     *
     * @param Subscription $subscription
     * @param float $amount
     * @param string $paymentMethod
     * @param Carbon|null $paymentDate
     * @param string|null $referenceNumber
     * @param string|null $notes
     * @return Payment
     */
    public function recordPayment(
        Subscription $subscription,
        float $amount,
        string|PaymentMethod $paymentMethod,
        ?Carbon $paymentDate = null,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use (
            $subscription,
            $amount,
            $paymentMethod,
            $paymentDate,
            $referenceNumber,
            $notes
        ) {
            $payment = new Payment([
                'company_id' => $subscription->company_id,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate ?? Carbon::now(),
                'reference_number' => $referenceNumber,
                'notes' => $notes,
            ]);
            
            $payment->save();

            $subscription->paid_amount += $amount;
            $subscription->remaining_amount = $subscription->net_amount - $subscription->paid_amount;
            $subscription->save();

            return $payment;
        });
    }

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription
     * @param string|null $reason
     * @return Subscription
     */
    public function cancelSubscription(Subscription $subscription, ?string $reason = null): Subscription
    {
        $subscription->status = SubscriptionStatus::CANCELLED;
        if ($reason !== null) {
            $subscription->notes = trim($subscription->notes . "\nCancel Reason: " . $reason);
        }
        $subscription->save();
        
        return $subscription;
    }

    /**
     * Freeze an active or soon-to-expire subscription.
     *
     * @param Subscription $subscription
     * @param string|null $reason
     * @return Subscription
     */
    public function freezeSubscription(Subscription $subscription, ?string $reason = null): Subscription
    {
        if (in_array($subscription->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::EXPIRING_SOON], true)) {
            $subscription->status = SubscriptionStatus::FROZEN;
            if ($reason !== null) {
                $subscription->notes = trim($subscription->notes . "\nFreeze Reason: " . $reason);
            }
            $subscription->save();
        }
        
        return $subscription;
    }

    /**
     * Unfreeze a subscription and re-determine its correct status.
     *
     * @param Subscription $subscription
     * @return Subscription
     */
    public function unfreezeSubscription(Subscription $subscription): Subscription
    {
        if ($subscription->status === SubscriptionStatus::FROZEN) {
            $subscription->status = $this->determineStatus($subscription);
            $subscription->save();
        }
        
        return $subscription;
    }
}
