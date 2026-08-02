<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Cities', function (Blueprint $table) {
            $table->uuid('CityID')->primary();
            $table->string('CityName');
            $table->foreignUuid('GovernorateID')->nullable()->constrained('Governorates', 'GovernorateID')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('Persons', function (Blueprint $table) {
            $table->foreign('CityID')->references('CityID')->on('Cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropForeign(['CityID']);
        });

        Schema::dropIfExists('Cities');
    }
};
