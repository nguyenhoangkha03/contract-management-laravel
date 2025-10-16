<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\GeneralConfiguration::create([
            'alert_anabled' => true,
            'alert_days_before' => 30,
            'round_total' => false,
        ]);
    }
}
