<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'input_mode')) {
                $table->string('input_mode', 20)->default('manual')->after('floor_plan_json');
            }
        });

        DB::table('projects')
            ->whereNotNull('floor_plan_json')
            ->where('floor_plan_json', '<>', '')
            ->update(['input_mode' => 'floorplan']);

        Schema::table('project_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('project_rooms', 'cad_room_id')) {
                $table->string('cad_room_id', 64)->nullable()->after('project_id');
                $table->index(['project_id', 'cad_room_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('project_rooms', 'cad_room_id')) {
                $table->dropIndex(['project_id', 'cad_room_id']);
                $table->dropColumn('cad_room_id');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'input_mode')) {
                $table->dropColumn('input_mode');
            }
        });
    }
};
