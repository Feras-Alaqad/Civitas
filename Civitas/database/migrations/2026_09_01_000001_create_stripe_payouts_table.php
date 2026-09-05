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
        Schema::create('Stripe_Payouts', function (Blueprint $table) {
            $table->id('PayoutID');
            $table->string('StripePayoutID', 64)->nullable()->unique();
            $table->decimal('Amount', 10, 2);
            $table->string('Currency', 3);
            $table->string('Status', 20)->default('pending');
            $table->string('Destination', 64)->nullable();
            $table->string('DestinationName')->nullable();
            $table->string('FailureReason')->nullable();
            $table->string('FailureCode', 64)->nullable();
            $table->timestamp('ArrivalDate')->nullable();
            $table->string('Description')->nullable();
            $table->unsignedBigInteger('RequestedBy')->nullable();
            $table->timestamps();

            $table->index('Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Stripe_Payouts');
    }
};