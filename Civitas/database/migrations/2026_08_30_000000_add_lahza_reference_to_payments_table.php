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
            $table->string('LahzaReference', 255)->nullable()->unique()->after('StripePaymentIntentID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->dropColumn('LahzaReference');
        });
    }
};
