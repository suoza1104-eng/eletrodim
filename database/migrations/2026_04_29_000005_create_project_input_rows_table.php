<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_input_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('circuit_number')->notNull();
            $table->string('room_type', 120)->notNull();
            $table->string('description', 180)->nullable();
            $table->enum('load_type', ['ILUMINAÇÃO', 'TUG', 'TUE', 'MISTO'])->notNull();
            $table->decimal('specific_power_va', 12, 2)->nullable();
            $table->decimal('area_m2', 12, 2)->nullable();
            $table->decimal('perimeter_m', 12, 2)->nullable();
            $table->decimal('power_factor', 6, 4)->default(1.0000);
            $table->integer('phases')->nullable();
            $table->integer('voltage')->nullable();
            $table->string('installation_method', 10)->nullable();
            $table->integer('grouped_circuits')->nullable();
            $table->integer('temperature_c')->nullable();
            $table->decimal('distance_m', 12, 2)->nullable();
            $table->decimal('voltage_drop_percent', 6, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_input_rows');
    }
};
