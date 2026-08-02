<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasIndex('Persons', 'idx_persons_gov_person')) {
            Schema::table('Persons', function (Blueprint $table) {
                $table->index(['GovernorateID', 'PersonID'], 'idx_persons_gov_person');
            });
        }

        if (!Schema::hasIndex('Service_Requests', 'idx_sr_person_type')) {
            Schema::table('Service_Requests', function (Blueprint $table) {
                $table->index(['PersonID', 'ServiceTypeID'], 'idx_sr_person_type');
            });
        }

        if (!Schema::hasIndex('Service_Requests', 'idx_sr_type_person_date')) {
            Schema::table('Service_Requests', function (Blueprint $table) {
                $table->index(['ServiceTypeID', 'PersonID', 'RequestDate'], 'idx_sr_type_person_date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropIndex('idx_persons_gov_person');
        });

        Schema::table('Service_Requests', function (Blueprint $table) {
            $table->dropIndex('idx_sr_person_type');
            $table->dropIndex('idx_sr_type_person_date');
        });
    }
};
