<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('LogID')->primary();
            $table->foreignUuid('UserID')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('ActionType', 50)->default('Login');
            $table->timestamp('Timestamp')->useCurrent();
            $table->string('IPAddress', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
