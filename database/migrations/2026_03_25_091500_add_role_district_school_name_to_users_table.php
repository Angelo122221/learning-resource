<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('teacher')->after('is_admin');
            }

            if (! Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'school_name')) {
                $table->string('school_name')->nullable()->after('district');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'school_name')) {
                $table->dropColumn('school_name');
            }

            if (Schema::hasColumn('users', 'district')) {
                $table->dropColumn('district');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
