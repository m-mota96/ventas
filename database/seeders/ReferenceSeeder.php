<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reference;

class ReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $references[] = [
            'name' => 'Abastecimiento de producto',
        ];
        
        $references[] = [
            'name' => 'Venta en sucusal',
        ];
        
        $references[] = [
            'name' => 'Ajuste de inventario',
        ];

        Reference::insert($references);
    }
}
