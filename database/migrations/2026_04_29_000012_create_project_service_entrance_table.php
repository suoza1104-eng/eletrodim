<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_service_entrance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->decimal('installed_load_kw', 12, 2)->nullable();
            $table->decimal('probable_demand_kva', 12, 2)->nullable();
            $table->integer('phases')->nullable();
            $table->string('pole_position', 80)->nullable();
            $table->string('supply_type', 120)->nullable();
            $table->string('supply_range', 120)->nullable();
            $table->string('wires', 80)->nullable();
            $table->integer('breaker_a')->nullable();
            $table->decimal('phase_conductor_mm2', 8, 2)->nullable();
            $table->decimal('protection_conductor_mm2', 8, 2)->nullable();
            $table->decimal('pvc_conduit_mm', 8, 2)->nullable();
            $table->decimal('steel_conduit_mm', 8, 2)->nullable();
            $table->string('grounding_conductor', 80)->nullable();
            $table->string('grounding_electrodes', 120)->nullable();
            $table->string('concrete_pole_type', 120)->nullable();
            $table->string('steel_pole_type', 120)->nullable();
            $table->string('pontalete_type', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_service_entrance');
    }
};
