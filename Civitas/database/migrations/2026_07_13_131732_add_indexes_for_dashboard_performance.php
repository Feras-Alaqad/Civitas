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
            $table->index('PaymentDate', 'idx_payments_date');
        });

        Schema::table('Service_Requests', function (Blueprint $table) {
            $table->index('RequestDate', 'idx_service_requests_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_date');
        });

        Schema::table('Service_Requests', function (Blueprint $table) {
            $table->dropIndex('idx_service_requests_date');
        });
    }
};
