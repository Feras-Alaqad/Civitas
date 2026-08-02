<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->char('GovernorateID', 36)->nullable()->after('CityID');

            $table->index('NationalID', 'idx_persons_national_id');
            $table->index('CityID', 'idx_persons_city_id');
            $table->index(['FullName', 'Phone', 'Email'], 'idx_persons_search');
        });
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropIndex('idx_persons_search');
            $table->dropIndex('idx_persons_city_id');
            $table->dropIndex('idx_persons_national_id');
            $table->dropColumn('GovernorateID');
        });
    }
};
