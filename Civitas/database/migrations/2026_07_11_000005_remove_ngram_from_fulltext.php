<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropIndex('ft_persons_full_name');
            $table->fullText(['FullName'], 'ft_persons_full_name');
        });
    }

    public function down(): void
    {
        Schema::table('Persons', function (Blueprint $table) {
            $table->dropIndex('ft_persons_full_name');
            $table->fullText(['FullName'], 'ft_persons_full_name')->parser('ngram');
        });
    }
};
