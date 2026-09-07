<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('project_rooms')->cascadeOnDelete();
            $table->string('load_type', 20);
            $table->string('description', 180)->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('power_va')->default(0);
            $table->integer('unit_va')->default(0);
            $table->boolean('is_auto')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('phases')->default(1);
            $table->integer('voltage_v')->default(127);
            $table->string('installation_method', 10)->default('B1');
            $table->integer('temperature_c')->default(30);
            $table->integer('grouped_circuits')->default(1);
            $table->decimal('fp', 4, 3)->default(1.000);
            $table->decimal('power_w', 12, 2)->nullable();
            $table->decimal('current_a', 10, 4)->nullable();
            $table->decimal('grouping_factor', 6, 4)->nullable();
            $table->decimal('temperature_factor', 6, 4)->nullable();
            $table->decimal('corrected_current_a', 10, 4)->nullable();
            $table->boolean('use_manual_va')->default(false);
            $table->integer('manual_va')->nullable();
            $table->integer('split_original_va')->nullable();
            $table->string('split_original_description', 180)->nullable();
            $table->integer('circuit_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_loads');
    }
};
