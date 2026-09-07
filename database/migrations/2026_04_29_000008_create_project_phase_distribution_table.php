<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_phase_distribution', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('circuit_number')->notNull();
            $table->boolean('use_phase_r')->default(false);
            $table->boolean('use_phase_s')->default(false);
            $table->boolean('use_phase_t')->default(false);
            $table->decimal('load_r_va', 12, 2)->default(0);
            $table->decimal('load_s_va', 12, 2)->default(0);
            $table->decimal('load_t_va', 12, 2)->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phase_distribution');
    }
};
