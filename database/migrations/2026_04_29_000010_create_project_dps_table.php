<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_dps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('dps_number')->notNull();
            $table->string('location_type', 120)->nullable();
            $table->integer('phase_neutral_voltage')->nullable();
            $table->decimal('short_circuit_current_ka', 12, 2)->nullable();
            $table->string('dps_class', 50)->nullable();
            $table->string('min_up', 50)->nullable();
            $table->decimal('uc_v', 12, 2)->nullable();
            $table->string('in_value', 50)->nullable();
            $table->string('iimp', 50)->nullable();
            $table->string('icc', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_dps');
    }
};
