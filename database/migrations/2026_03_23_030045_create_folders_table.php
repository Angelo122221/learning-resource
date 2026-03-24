<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This table handles both top-level folders and nested subfolders.
     */
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            
            // The display name of the folder
            $table->string('name'); 
            
            // The Parent ID allows a folder to live inside another folder.
            // constrained('folders') ensures the parent actually exists.
            // onDelete('cascade') means if you delete a parent, all subfolders are deleted too.
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('folders')
                  ->onDelete('cascade');
                  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key first to avoid integrity constraints, then drop table
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        
        Schema::dropIfExists('folders');
    }
};