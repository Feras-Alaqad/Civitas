<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Service_Requests', function (Blueprint $table) {
            $table->foreignUuid('PersonID')->nullable()->constrained('Persons', 'PersonID')->nullOnDelete();
            $table->foreignUuid('UserID')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignUuid('ServiceTypeID')->nullable()->change();
            $table->foreign('ServiceTypeID')->references('ServiceTypeID')->on('Service_Types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('Service_Requests', function (Blueprint $table) {
            $table->dropForeign(['ServiceTypeID']);
            $table->dropColumn(['PersonID', 'UserID']);
            $table->uuid('ServiceTypeID')->nullable()->change();
        });
    }
};
