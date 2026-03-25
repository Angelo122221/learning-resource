<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add description to folders
        Schema::table('folders', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        // 2. Add preview image to files
        Schema::table('resource_files', function (Blueprint $table) {
            $table->string('preview_image_path')->nullable()->after('file_path');
        });

        // 3. Create the Carousel table
        Schema::create('carousel_images', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_path');
            $table->timestamps();
        });

        // 4. Create the Featured Videos table
        Schema::create('featured_videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_link');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. Create the Tracking/Analytics table
        Schema::create('resource_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Can be a file OR a folder action
            $table->foreignId('folder_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('resource_file_id')->nullable(); 
            $table->string('action'); // e.g., 'viewed_folder', 'downloaded_file'
            $table->timestamps();
            
            // Assuming your files table is named `resource_files`
            $table->foreign('resource_file_id')->references('id')->on('resource_files')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_trackings');
        Schema::dropIfExists('featured_videos');
        Schema::dropIfExists('carousel_images');
        
        Schema::table('resource_files', function (Blueprint $table) {
            $table->dropColumn('preview_image_path');
        });
        
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
