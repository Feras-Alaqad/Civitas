<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->string('PayPalOrderID', 100)->nullable()->after('ReceiptNumber');
            $table->string('PayPalPayerID', 100)->nullable()->after('PayPalOrderID');
            $table->string('Status', 50)->default('Completed')->after('PayPalPayerID');
        });
    }

    public function down(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->dropColumn(['PayPalOrderID', 'PayPalPayerID', 'Status']);
        });
    }
};
