<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('report_type', ['pdf', 'print', 'share'])->notNull();
            $table->string('file_path', 255)->nullable();
            $table->string('share_token', 80)->nullable();
            $table->dateTime('generated_at')->notNull();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_reports');
    }
};
