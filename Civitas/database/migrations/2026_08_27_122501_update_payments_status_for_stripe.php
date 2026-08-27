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
            $table->string('Status', 20)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Payments', function (Blueprint $table) {
            $table->string('Status', 50)->default('Completed')->change();
        });
    }
};
