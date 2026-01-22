<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payment_methods[] = [
            'payment_method' => 'Efectivo',
            'default'        => 1
        ];
        
        $payment_methods[] = [
            'payment_method' => 'Tarjeta',
            'default'        => 0
        ];
        
        $payment_methods[] = [
            'payment_method' => 'Efectivo y Tarjeta',
            'default'        => 0
        ];

        PaymentMethod::insert($payment_methods);
    }
}
