<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Payments', function (Blueprint $table) {
            $table->uuid('PaymentID')->primary();
            $table->foreignUuid('RequestID')->constrained('Service_Requests', 'RequestID')->cascadeOnDelete();
            $table->decimal('Amount', 10, 2);
            $table->timestamp('PaymentDate')->useCurrent();
            $table->string('ReceiptNumber', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Payments');
    }
};
