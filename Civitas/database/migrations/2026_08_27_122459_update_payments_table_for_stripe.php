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
        Schema::table('Payments', function (Blueprint $table) {
            $table->dropColumn(['PayPalOrderID', 'PayPalPayerID']);

            $table->string('Currency', 3)->default('USD')->after('Amount');
            $table->string('StripePaymentIntentID', 255)->nullable()->unique()->after('ReceiptNumber');
            $table->json('Metadata')->nullable()->after('Status');
            $table->timestamp('PaidAt')->nullable()->after('Metadata');
            $table->string('FailureReason')->nullable()->after('PaidAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->dropColumn(['Currency', 'StripePaymentIntentID', 'Metadata', 'PaidAt', 'FailureReason']);
            $table->string('PayPalOrderID', 100)->nullable()->after('ReceiptNumber');
            $table->string('PayPalPayerID', 100)->nullable()->after('PayPalOrderID');
        });
    }
};
