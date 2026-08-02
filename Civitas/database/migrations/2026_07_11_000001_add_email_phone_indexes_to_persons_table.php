<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->index('Email', 'idx_persons_email');
            $table->index('Phone', 'idx_persons_phone');
            $table->fullText(['FullName', 'NationalID', 'Phone', 'Email'], 'ft_persons_search');
        });
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropIndex('idx_persons_email');
            $table->dropIndex('idx_persons_phone');
            $table->dropIndex('ft_persons_search');
        });
    }
};
