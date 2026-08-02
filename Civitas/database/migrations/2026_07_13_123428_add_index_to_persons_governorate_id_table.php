<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->index('GovernorateID', 'idx_persons_governorate_id');
        });
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropIndex('idx_persons_governorate_id');
        });
    }
};
