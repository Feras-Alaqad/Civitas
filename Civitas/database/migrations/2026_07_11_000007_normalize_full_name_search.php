<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "UPDATE Persons SET FullNameSearch = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(FullNameSearch, 'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ؤ', 'و'), 'ئ', 'ي'), 'ة', 'ه'), 'ى', 'ي')"
        );
    }

    public function down(): void {}
};
