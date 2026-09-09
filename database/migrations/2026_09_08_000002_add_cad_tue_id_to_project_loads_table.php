<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_loads', function (Blueprint $table) {
            $table->string('cad_tue_id', 64)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('project_loads', function (Blueprint $table) {
            $table->dropColumn('cad_tue_id');
        });
    }
};
