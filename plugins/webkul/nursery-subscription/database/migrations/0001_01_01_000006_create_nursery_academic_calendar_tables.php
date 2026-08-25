<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursery_academic_years', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 50); // e.g. "2026-2027"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_current']);
        });

        Schema::create('nursery_academic_terms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('nursery_academic_years')->cascadeOnDelete();
            $table->string('name', 100); // e.g. "الفصل الدراسي الأول"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'academic_year_id']);
        });

        Schema::create('nursery_holidays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('nursery_academic_years')->nullOnDelete();
            $table->string('name', 150); // e.g. "إجازة اليوم الوطني"
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days_count')->default(1);
            $table->boolean('affects_subscriptions')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursery_holidays');
        Schema::dropIfExists('nursery_academic_terms');
        Schema::dropIfExists('nursery_academic_years');
    }
};
