<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursery_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name', 255);
            $table->date('birth_date');
            $table->string('gender', 10)->nullable();
            $table->string('guardian_name', 255);
            $table->string('guardian_phone', 20)->index();
            $table->string('emergency_contact', 255)->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->boolean('has_siblings')->default(false);
            $table->text('medical_notes')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'guardian_phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nursery_children');
    }
};
