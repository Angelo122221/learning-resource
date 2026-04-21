<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testUser = User::firstOrNew(['email' => 'test.user@deped.gov.ph']);

        $testUser->name = 'Test User';
        $testUser->is_admin = false;
        $testUser->role = 'teacher';
        $testUser->district = 'Sample District';
        $testUser->school_name = 'Sample School';
        $testUser->email_verified_at = $testUser->email_verified_at ?? now();

        // Keep test login stable on first seed while avoiding password resets on re-seed.
        if (! $testUser->exists) {
            $testUser->password = Hash::make('test12345');
        }

        $testUser->save();
    }
}
