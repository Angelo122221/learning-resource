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
        User::updateOrCreate(
            ['email' => 'admin@deped.gov.ph'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('admin12345'),
                'is_admin' => true,
                'role' => 'admin',
                'district' => 'Division Office',
                'school_name' => 'DepEd Central Office',
                'email_verified_at' => now(),
            ]
        );
    }
}
