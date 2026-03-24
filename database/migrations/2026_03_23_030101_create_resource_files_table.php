<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_files', function (Blueprint $table) {
            $table->id();
            // This is the missing column! It links the file to a folder.
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            
            $table->string('title');
            $table->string('file_path');
            $table->string('file_type');
            $table->boolean('is_locked')->default(false);
            $table->datetime('opens_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_files');
    }
};