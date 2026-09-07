<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_load_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('input_row_id')->constrained('project_input_rows')->cascadeOnDelete();
            $table->integer('circuit_number')->notNull();
            $table->string('room_description', 180)->nullable();
            $table->decimal('lighting_va', 12, 2)->default(0);
            $table->integer('outlet_100_qty')->default(0);
            $table->integer('outlet_600_qty')->default(0);
            $table->integer('outlet_1000_qty')->default(0);
            $table->decimal('tue_va', 12, 2)->default(0);
            $table->decimal('power_factor', 6, 4)->default(1.0000);
            $table->decimal('power_w', 12, 2)->default(0);
            $table->decimal('power_va', 12, 2)->default(0);
            $table->integer('phases')->nullable();
            $table->integer('voltage')->nullable();
            $table->decimal('project_current_a', 12, 4)->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_load_rows');
    }
};
