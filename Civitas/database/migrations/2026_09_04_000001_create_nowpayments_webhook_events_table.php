<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nowpayments_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('PaymentID', 64)->unique()->comment('NOWPayments payment_id (idempotency key)');
            $table->string('OrderID', 64)->nullable();
            $table->json('Payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nowpayments_webhook_events');
    }
};
