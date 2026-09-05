<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['Governorates' => 'GovernorateID', 'Cities' => 'CityID', 'Nationalities' => 'NationalityID'] as $table => $primary) {
            if (Schema::hasColumn($table, 'source_code')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->integer('source_code')->nullable();
            });

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->unique('source_code', "uq_{$table}_source_code");
            });
        }
    }

    public function down(): void
    {
        foreach (['Governorates', 'Cities', 'Nationalities'] as $table) {
            if (!Schema::hasColumn($table, 'source_code')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex("uq_{$table}_source_code");
                $blueprint->dropColumn('source_code');
            });
        }
    }
};