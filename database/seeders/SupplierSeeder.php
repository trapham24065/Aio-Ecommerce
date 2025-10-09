<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name'     => 'Guardian Vietnam',
                'code'     => 'guardian-vn',
                'home_url' => 'https://www.guardian.com.vn/',
                'status'   => 1,
            ],
            [
                'name'     => 'Watsons Vietnam',
                'code'     => 'watsons-vn',
                'home_url' => 'https://www.watsons.vn/',
                'status'   => 1,
            ],
            [
                'name'     => 'Nhà phân phối Hasaki',
                'code'     => 'hasaki-dist',
                'home_url' => 'https://hasaki.vn/',
                'status'   => 1,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }

}
