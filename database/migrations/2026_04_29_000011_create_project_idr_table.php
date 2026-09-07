<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_idr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('idr_number')->notNull();
            $table->integer('phases')->nullable();
            $table->enum('has_neutral', ['SIM', 'NÃO'])->nullable();
            $table->integer('breaker_in_a')->nullable();
            $table->decimal('short_circuit_current_ka', 12, 2)->nullable();
            $table->integer('nominal_current_a')->nullable();
            $table->string('poles', 20)->nullable();
            $table->string('residual_current', 20)->nullable();
            $table->string('icc', 50)->nullable();
            $table->string('idr_status', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_idr');
    }
};
