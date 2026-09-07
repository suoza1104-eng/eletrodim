<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'student'])->default('student')->notNull();
            $table->string('name', 150)->notNull();
            $table->string('email', 180)->unique()->notNull();
            $table->string('phone', 30)->nullable();
            $table->string('password', 255)->notNull();
            $table->enum('status', ['active', 'blocked', 'cancelled', 'expired'])->default('active')->notNull();
            $table->dateTime('access_starts_at')->nullable();
            $table->dateTime('access_expires_at')->nullable();
            $table->dateTime('first_login_at')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
