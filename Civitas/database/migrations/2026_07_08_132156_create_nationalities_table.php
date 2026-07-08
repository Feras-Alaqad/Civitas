<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Nationalities', function (Blueprint $table) {
            $table->uuid('NationalityID')->primary();
            $table->string('NationalityName');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Nationalities');
    }
};
