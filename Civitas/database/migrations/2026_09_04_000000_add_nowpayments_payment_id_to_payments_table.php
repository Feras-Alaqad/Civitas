<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->string('NowPaymentsPaymentID', 64)->nullable()->unique()->after('LahzaReference');
        });
    }

    public function down(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->dropColumn('NowPaymentsPaymentID');
        });
    }
};
