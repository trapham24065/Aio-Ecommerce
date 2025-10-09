<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'L\'Oréal', 'code' => 'loreal', 'status' => 1],
            ['name' => 'Dior', 'code' => 'dior', 'status' => 1],
            ['name' => 'Shiseido', 'code' => 'shiseido', 'status' => 1],
            ['name' => 'The Ordinary', 'code' => 'the-ordinary', 'status' => 1],
            ['name' => 'Innisfree', 'code' => 'innisfree', 'status' => 1],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }

}
