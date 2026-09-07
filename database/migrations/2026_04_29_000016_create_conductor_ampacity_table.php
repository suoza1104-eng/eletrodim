<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conductor_ampacity', function (Blueprint $table) {
            $table->id();
            $table->string('installation_method', 5)->notNull();
            $table->string('phase_group', 5)->notNull();
            $table->decimal('conductor_mm2', 6, 2)->notNull();
            $table->decimal('iz_amperes', 8, 2)->notNull();

            $table->unique(['installation_method', 'phase_group', 'conductor_mm2'], 'ampacity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductor_ampacity');
    }
};
