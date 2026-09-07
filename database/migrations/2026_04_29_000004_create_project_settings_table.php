<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('default_voltage')->nullable();
            $table->integer('default_phases')->nullable();
            $table->decimal('default_power_factor', 6, 4)->default(1.0000);
            $table->string('default_installation_method', 10)->nullable();
            $table->integer('default_temperature_c')->nullable();
            $table->decimal('default_voltage_drop_percent', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_settings');
    }
};
