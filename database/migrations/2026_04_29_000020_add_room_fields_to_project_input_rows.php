<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_input_rows', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('project_id')
                  ->constrained('project_rooms')->nullOnDelete();
            $table->integer('quantity')->default(1)->after('specific_power_va');
            $table->boolean('is_below_minimum')->default(false)->after('quantity');
            $table->decimal('minimum_va', 12, 2)->nullable()->after('is_below_minimum');
            $table->string('tue_description', 180)->nullable()->after('minimum_va');
        });
    }

    public function down(): void
    {
        Schema::table('project_input_rows', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn(['room_id', 'quantity', 'is_below_minimum', 'minimum_va', 'tue_description']);
        });
    }
};
