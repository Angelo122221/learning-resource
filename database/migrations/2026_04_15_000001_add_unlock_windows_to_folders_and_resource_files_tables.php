<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('folders')) {
            Schema::table('folders', function (Blueprint $table) {
                if (! Schema::hasColumn('folders', 'unlock_starts_at')) {
                    $table->timestamp('unlock_starts_at')->nullable()->after('is_locked');
                }

                if (! Schema::hasColumn('folders', 'unlock_ends_at')) {
                    $table->timestamp('unlock_ends_at')->nullable()->after('unlock_starts_at');
                }
            });
        }

        if (Schema::hasTable('resource_files')) {
            Schema::table('resource_files', function (Blueprint $table) {
                if (! Schema::hasColumn('resource_files', 'unlock_starts_at')) {
                    $table->timestamp('unlock_starts_at')->nullable()->after('is_locked');
                }

                if (! Schema::hasColumn('resource_files', 'unlock_ends_at')) {
                    $table->timestamp('unlock_ends_at')->nullable()->after('unlock_starts_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('resource_files')) {
            Schema::table('resource_files', function (Blueprint $table) {
                if (Schema::hasColumn('resource_files', 'unlock_ends_at')) {
                    $table->dropColumn('unlock_ends_at');
                }

                if (Schema::hasColumn('resource_files', 'unlock_starts_at')) {
                    $table->dropColumn('unlock_starts_at');
                }
            });
        }

        if (Schema::hasTable('folders')) {
            Schema::table('folders', function (Blueprint $table) {
                if (Schema::hasColumn('folders', 'unlock_ends_at')) {
                    $table->dropColumn('unlock_ends_at');
                }

                if (Schema::hasColumn('folders', 'unlock_starts_at')) {
                    $table->dropColumn('unlock_starts_at');
                }
            });
        }
    }
};
