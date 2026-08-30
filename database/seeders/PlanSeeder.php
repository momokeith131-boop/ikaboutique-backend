<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'basic',
                'display_name' => 'Basique',
                'price' => 5000,
                'prices' => [
                    '1_month' => 5000,
                    '3_months' => 14000,
                    '6_months' => 27000,
                    '1_year' => 50000,
                ],
                'currency' => 'FCFA',
                'features' => [
                    'max_products' => 12,
                    'max_orders' => 50,
                    'support' => false,
                    'analytics' => false,
                ],
            ],
            [
                'name' => 'standard',
                'display_name' => 'Standard',
                'price' => 7500,
                'prices' => [
                    '1_month' => 7500,
                    '3_months' => 21000,
                    '6_months' => 40000,
                    '1_year' => 75000,
                ],
                'currency' => 'FCFA',
                'features' => [
                    'max_products' => -1,
                    'max_orders' => 500,
                    'support' => true,
                    'analytics' => false,
                ],
            ],
            [
                'name' => 'premium',
                'display_name' => 'Premium',
                'price' => 10000,
                'prices' => [
                    '1_month' => 10000,
                    '3_months' => 28000,
                    '6_months' => 54000,
                    '1_year' => 100000,
                ],
                'currency' => 'FCFA',
                'features' => [
                    'max_products' => -1,
                    'max_orders' => -1,
                    'support' => true,
                    'analytics' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
