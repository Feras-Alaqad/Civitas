<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Stripe_Webhook_Events', function (Blueprint $table) {
            $table->id();
            $table->string('EventID', 64)->unique();
            $table->string('EventType', 64);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Stripe_Webhook_Events');
    }
};
