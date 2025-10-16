<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            for ($j = 1; $j <= 15; $j++) {
                Permission::create([
                    'role_id' => $i,
                    'feature_id' => $j,
                    'enable' => true
                ]);
            }
        }
    }
}
