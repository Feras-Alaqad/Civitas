<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Attachments', function (Blueprint $table) {
            $table->uuid('AttachmentID')->primary();
            $table->foreignUuid('RequestID')->constrained('Service_Requests', 'RequestID')->cascadeOnDelete();
            $table->string('FilePath');
            $table->string('DocumentType', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Attachments');
    }
};
