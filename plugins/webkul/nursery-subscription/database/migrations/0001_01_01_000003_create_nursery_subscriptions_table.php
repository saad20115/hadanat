<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursery_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('child_id')->constrained('nursery_children')->cascadeOnDelete();
            $table->foreignId('pricing_plan_id')->constrained('nursery_pricing_plans')->restrictOnDelete();
            $table->foreignId('renewal_of_id')->nullable()->constrained('nursery_subscriptions')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('base_price', 10, 2);
            $table->decimal('sibling_discount_pct', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->boolean('includes_tshirt')->default(false);
            $table->decimal('tshirt_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->string('status', 20)->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['child_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursery_subscriptions');
    }
};
