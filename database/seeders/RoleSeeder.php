<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'code' => 'quanly',
                'name' => 'Quản lý',
                'description' => 'Quản lý phòng ban',
            ],
            [
                'code' => 'kinhdoanh',
                'name' => 'Kinh doanh',
                'description' => 'Nhân viên kinh doanh',
            ],
            [
                'code' => 'hopdong',
                'name' => 'Hợp đồng',
                'description' => 'Nhân viên hợp đồng',
            ],
            [
                'code' => 'ketoan',
                'name' => 'Kế toán',
                'description' => 'Nhân viên kế toán',
            ],
            [
                'code' => 'trienkhai',
                'name' => 'Triển khai',
                'description' => 'Nhân viên triển khai',
            ],
            [
                'code' => 'theodoi',
                'name' => 'Người theo dõi',
                'description' => 'Người theo dõi hợp đồng',
            ],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::create($role);
        }
    }
}
