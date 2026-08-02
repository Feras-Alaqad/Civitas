<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('Persons', 'FullNameSearch')) {
            Schema::table('Persons', function (Blueprint $table) {
                $table->string('FullNameSearch', 500)->nullable()->after('FullName');
            });
        }

        DB::statement('UPDATE Persons SET FullNameSearch = FullName WHERE FullNameSearch IS NULL OR FullNameSearch = ""');
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropColumn('FullNameSearch');
        });
    }
};
