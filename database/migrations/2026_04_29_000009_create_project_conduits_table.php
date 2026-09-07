<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_conduits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->integer('circuit_number')->notNull();
            $table->integer('curves_90')->default(0);
            $table->decimal('length_m', 12, 2)->nullable();
            $table->string('mounting_type', 80)->nullable();
            $table->integer('qty_1_5')->default(0);
            $table->integer('qty_2_5')->default(0);
            $table->integer('qty_4')->default(0);
            $table->integer('qty_6')->default(0);
            $table->integer('qty_10')->default(0);
            $table->integer('qty_16')->default(0);
            $table->integer('qty_25')->default(0);
            $table->integer('qty_35')->default(0);
            $table->integer('qty_50')->default(0);
            $table->integer('qty_70')->default(0);
            $table->integer('qty_95')->default(0);
            $table->integer('total_cables')->default(0);
            $table->decimal('dimension_mm', 8, 2)->nullable();
            $table->string('dimension_inch', 20)->nullable();
            $table->string('max_occupation', 50)->nullable();
            $table->string('used_conduit', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_conduits');
    }
};
