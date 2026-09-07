<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_in_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 120)->unique()->nullable();
            $table->string('source', 120)->nullable();
            $table->string('event_type', 120)->nullable();
            $table->enum('process_status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('process_message')->nullable();
            $table->longText('payload_json')->notNull();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_in_events');
    }
};
