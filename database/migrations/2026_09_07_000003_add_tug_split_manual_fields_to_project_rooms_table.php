<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('project_rooms', 'tug_qty_600_manual')) {
                $table->integer('tug_qty_600_manual')->nullable()->after('tug_qty_manual');
            }
            if (!Schema::hasColumn('project_rooms', 'tug_qty_100_manual')) {
                $table->integer('tug_qty_100_manual')->nullable()->after('tug_qty_600_manual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('project_rooms', 'tug_qty_600_manual')) {
                $table->dropColumn(['tug_qty_600_manual', 'tug_qty_100_manual']);
            }
        });
    }
};
