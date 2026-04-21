<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@deped.gov.ph']);

        $admin->name = 'System Admin';
        $admin->is_admin = true;
        $admin->role = 'admin';
        $admin->district = 'Division Office';
        $admin->school_name = 'DepEd Central Office';
        $admin->email_verified_at = $admin->email_verified_at ?? now();

        // Keep admin login stable on first seed while avoiding password resets on re-seed.
        if (! $admin->exists) {
            $admin->password = Hash::make('admin12345');
        }

        $admin->save();
    }
}
