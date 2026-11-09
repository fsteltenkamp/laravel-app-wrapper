<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->text('config');
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('last_failed_at')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_channel_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->unique(['notification_channel_id', 'event']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_subscriptions');
        Schema::dropIfExists('notification_channels');
    }
};
