<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_circuit_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('circuit_number')->notNull();
            $table->text('description')->nullable();
            $table->string('circuit_type', 180)->nullable();
            $table->decimal('lighting_va', 12, 2)->default(0);
            $table->integer('outlet_100_qty')->default(0);
            $table->integer('outlet_600_qty')->default(0);
            $table->integer('outlet_1000_qty')->default(0);
            $table->decimal('tue_va', 12, 2)->default(0);
            $table->decimal('power_factor', 6, 4)->default(1.0000);
            $table->decimal('power_w', 12, 2)->default(0);
            $table->decimal('power_va', 12, 2)->default(0);
            $table->decimal('demand_factor_calculated', 8, 4)->nullable();
            $table->decimal('demand_factor_manual', 8, 4)->nullable();
            $table->boolean('use_manual_demand_factor')->default(false);
            $table->decimal('demand_va_calculated', 12, 2)->default(0);
            $table->decimal('demand_va_manual', 12, 2)->nullable();
            $table->boolean('use_manual_demand_va')->default(false);
            $table->integer('phases')->nullable();
            $table->integer('voltage')->nullable();
            $table->decimal('project_current_a', 12, 4)->default(0);
            $table->string('installation_method', 10)->nullable();
            $table->integer('grouped_circuits')->nullable();
            $table->decimal('grouping_factor', 8, 4)->nullable();
            $table->integer('temperature_c')->nullable();
            $table->decimal('temperature_factor', 8, 4)->nullable();
            $table->decimal('corrected_current_a', 12, 4)->nullable();
            $table->decimal('distance_m', 12, 2)->nullable();
            $table->decimal('voltage_drop_percent', 6, 2)->nullable();
            $table->decimal('min_conductor_mm2', 8, 2)->nullable();
            $table->decimal('ampacity_conductor_mm2', 8, 2)->nullable();
            $table->decimal('voltage_drop_conductor_mm2', 8, 2)->nullable();
            $table->decimal('final_conductor_calculated_mm2', 8, 2)->nullable();
            $table->decimal('final_conductor_manual_mm2', 8, 2)->nullable();
            $table->boolean('use_manual_final_conductor')->default(false);
            $table->decimal('iz_a', 12, 4)->nullable();
            $table->integer('breaker_calculated_a')->nullable();
            $table->integer('breaker_manual_a')->nullable();
            $table->boolean('use_manual_breaker')->default(false);
            $table->string('breaker_curve', 10)->default('C');
            $table->string('breaker_model', 50)->default('DIN');
            $table->decimal('icc_ka', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('calc_status', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_circuit_calculations');
    }
};
