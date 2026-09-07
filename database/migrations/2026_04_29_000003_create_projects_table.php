<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 180)->notNull();
            $table->string('client_name', 180)->nullable();
            $table->string('client_phone', 30)->nullable();
            $table->string('client_email', 180)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 50)->nullable();
            $table->text('observations')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft')->notNull();
            $table->integer('progress_step')->default(1)->notNull();
            $table->decimal('progress_percent', 5, 2)->default(0)->notNull();
            $table->string('share_token', 80)->unique()->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
