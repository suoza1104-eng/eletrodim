<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            $table->integer('lighting_va_calculated')->nullable()->after('sort_order');
            $table->integer('lighting_va_manual')->nullable()->after('lighting_va_calculated');
            $table->boolean('use_manual_lighting')->default(false)->after('lighting_va_manual');
            $table->string('lighting_rule_description', 255)->nullable()->after('use_manual_lighting');

            $table->string('tug_rule_group', 60)->nullable()->after('lighting_rule_description');
            $table->integer('tug_qty_calculated')->nullable()->after('tug_rule_group');
            $table->integer('tug_qty_manual')->nullable()->after('tug_qty_calculated');
            $table->boolean('use_manual_tug_qty')->default(false)->after('tug_qty_manual');
            $table->integer('tug_va_calculated')->nullable()->after('use_manual_tug_qty');
            $table->integer('tug_va_manual')->nullable()->after('tug_va_calculated');
            $table->boolean('use_manual_tug_va')->default(false)->after('tug_va_manual');
            $table->string('tug_rule_description', 255)->nullable()->after('use_manual_tug_va');

            $table->integer('total_minimum_va_calculated')->nullable()->after('tug_rule_description');
            $table->integer('total_minimum_va_final')->nullable()->after('total_minimum_va_calculated');
        });
    }

    public function down(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            $table->dropColumn([
                'lighting_va_calculated',
                'lighting_va_manual',
                'use_manual_lighting',
                'lighting_rule_description',
                'tug_rule_group',
                'tug_qty_calculated',
                'tug_qty_manual',
                'use_manual_tug_qty',
                'tug_va_calculated',
                'tug_va_manual',
                'use_manual_tug_va',
                'tug_rule_description',
                'total_minimum_va_calculated',
                'total_minimum_va_final',
            ]);
        });
    }
};
