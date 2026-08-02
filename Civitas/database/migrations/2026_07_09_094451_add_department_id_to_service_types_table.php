<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Service_Types', function (Blueprint $table) {
            $table->foreignUuid('DepartmentID')->nullable()->after('ServiceName')->constrained('Departments', 'DepartmentID')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('Service_Types', function (Blueprint $table) {
            $table->dropForeign(['DepartmentID']);
            $table->dropColumn('DepartmentID');
        });
    }
};
