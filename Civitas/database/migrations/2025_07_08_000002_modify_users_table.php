<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'email_verified_at', 'password', 'remember_token']);
            $table->string('Username', 255)->unique()->after('id');
            $table->string('PasswordHash', 255)->after('Username');
            $table->foreignUuid('RoleID')->nullable()->constrained('roles', 'RoleID')->nullOnDelete();
            $table->boolean('IsActive')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['Username', 'PasswordHash', 'RoleID', 'IsActive']);
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }
};
