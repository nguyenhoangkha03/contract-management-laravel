<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Chờ duyệt', 'code' => 'choduyet'],
            ['name' => 'Đã duyệt', 'code' => 'daduyet'],
            ['name' => 'Dự thảo', 'code' => 'duthao'],
            ['name' => 'Thương thảo', 'code' => 'thuongthao'],
            ['name' => 'Trình ký', 'code' => 'trinhky'],
            ['name' => 'Đã ký', 'code' => 'daky'],
        ];

        foreach ($statuses as $status) {
            \App\Models\ContractStatus::create($status);
        }
    }
}
