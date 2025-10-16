<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['code' => 'capnhatthongtin', 'name' => 'Cập nhật thông tin', 'description' => 'Cập nhật thông tin hợp đồng'],
            ['code' => 'hanghoa', 'name' => 'Hàng hóa', 'description' => 'Quản lý hàng hóa trong hợp đồng'],
            ['code' => 'xetduyet', 'name' => 'Xét duyệt', 'description' => 'Xét duyệt hợp đồng'],
            ['code' => 'duthao', 'name' => 'Dự thảo', 'description' => 'Tạo dự thảo hợp đồng'],
            ['code' => 'thuongthao', 'name' => 'Thương thảo', 'description' => 'Thương thảo hợp đồng'],
            ['code' => 'trinhky', 'name' => 'Trình ký', 'description' => 'Trình ký hợp đồng'],
            ['code' => 'daky', 'name' => 'Đã ký', 'description' => 'Quản lý hợp đồng đã ký'],
            ['code' => 'tinhtrangthuchien', 'name' => 'Tình trạng thực hiện', 'description' => 'Cập nhật tình trạng thực hiện'],
            ['code' => 'dieuchinh', 'name' => 'Điều chỉnh', 'description' => 'Điều chỉnh hợp đồng'],
            ['code' => 'thanhly', 'name' => 'Thanh lý', 'description' => 'Thanh lý hợp đồng'],
            ['code' => 'thanhtoan', 'name' => 'Thanh toán', 'description' => 'Quản lý thanh toán'],
            ['code' => 'hoadon', 'name' => 'Hóa đơn', 'description' => 'Quản lý hóa đơn'],
            ['code' => 'dinhkem', 'name' => 'Đính kèm', 'description' => 'Quản lý file đính kèm'],
            ['code' => 'ghichu', 'name' => 'Ghi chú', 'description' => 'Quản lý ghi chú hợp đồng'],
            ['code' => 'xoa', 'name' => 'Xóa', 'description' => 'Xóa hợp đồng'],
        ];

        foreach ($features as $feature) {
            \App\Models\Feature::create($feature);
        }
    }
}
