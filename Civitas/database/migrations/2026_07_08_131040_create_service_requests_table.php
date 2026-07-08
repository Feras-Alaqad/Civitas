<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Service_Requests', function (Blueprint $table) {
            $table->uuid('RequestID')->primary();
            $table->uuid('ServiceTypeID')->nullable();
            $table->timestamp('RequestDate')->useCurrent();
            $table->string('Status', 50)->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Service_Requests');
    }
};
