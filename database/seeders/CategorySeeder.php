<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Chăm sóc da', 'code' => 'skincare', 'status' => 1],
            ['name' => 'Trang điểm', 'code' => 'makeup', 'status' => 1],
            ['name' => 'Chăm sóc tóc', 'code' => 'hair-care', 'status' => 1],
            ['name' => 'Nước hoa', 'code' => 'fragrance', 'status' => 1],
            ['name' => 'Chăm sóc cơ thể', 'code' => 'bath-body', 'status' => 0], // Ví dụ một danh mục không hoạt động
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }

}
