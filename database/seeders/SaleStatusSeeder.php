<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SaleStatus;

class SaleStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status[] = [
            'name' => 'Activa',
        ];
        
        $status[] = [
            'name' => 'Cancelada',
        ];
        
        $status[] = [
            'name' => 'Eliminada',
        ];

        SaleStatus::insert($status);
    }
}
