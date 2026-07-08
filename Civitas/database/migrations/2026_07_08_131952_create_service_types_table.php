<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Service_Types', function (Blueprint $table) {
            $table->uuid('ServiceTypeID')->primary();
            $table->string('ServiceName');
            $table->decimal('Fees', 10, 2)->default(0);
            $table->text('RequiredDocuments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Service_Types');
    }
};
