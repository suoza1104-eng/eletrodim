<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'floors_count')) {
                $table->integer('floors_count')->default(1)->after('observations');
            }
        });

        Schema::table('project_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('project_rooms', 'floor_number')) {
                $table->integer('floor_number')->default(1)->after('sort_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'floors_count')) {
                $table->dropColumn('floors_count');
            }
        });

        Schema::table('project_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('project_rooms', 'floor_number')) {
                $table->dropColumn('floor_number');
            }
        });
    }
};
