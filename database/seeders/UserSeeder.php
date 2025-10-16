<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        \App\Models\User::create([
            'name' => 'Quản lý',
            'email' => 'quanly@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        \App\Models\User::create([
            'name' => 'Kinh doanh',
            'email' => 'kinhdoanh@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }
}
