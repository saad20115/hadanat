<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursery_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('age_stage', 20);
            $table->string('stage_label', 100);
            $table->string('duration_type', 20);
            $table->unsignedTinyInteger('hours_per_day')->nullable();
            $table->decimal('duration_value', 5, 2)->nullable();
            $table->string('duration_label', 150);
            $table->unsignedInteger('visits_count')->nullable();
            $table->unsignedTinyInteger('visits_period_months')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'age_stage', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursery_pricing_plans');
    }
};
