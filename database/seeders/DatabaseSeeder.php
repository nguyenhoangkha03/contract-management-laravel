<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed trong thứ tự: general_config -> roles -> features -> contract_statuses -> permissions -> notifications -> users
        $this->call([
            GeneralConfigurationSeeder::class,
            RoleSeeder::class,
            FeatureSeeder::class,
            ContractStatusSeeder::class,
            PermissionSeeder::class,
            GeneralConfigurationNotificationSeeder::class,
            UserSeeder::class,
        ]);
    }
}
