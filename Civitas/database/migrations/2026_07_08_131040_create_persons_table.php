<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Persons', function (Blueprint $table) {
            $table->uuid('PersonID')->primary();
            $table->string('FullName');
            $table->date('DateOfBirth')->nullable();
            $table->string('NationalID', 50)->nullable()->unique();
            $table->text('Address')->nullable();
            $table->string('Gender', 10)->nullable();
            $table->uuid('NationalityID')->nullable();
            $table->uuid('CityID')->nullable();
            $table->string('Phone', 30)->nullable();
            $table->string('Email', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Persons');
    }
};
