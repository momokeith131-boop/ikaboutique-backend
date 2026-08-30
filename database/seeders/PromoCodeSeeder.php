<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'BASIC2000',
                'target_plan' => 'basic',
                'discount_amount' => 2000,
                'duration' => 1,
                'usage_limit' => 100,
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'code' => 'STANDARD2500',
                'target_plan' => 'standard',
                'discount_amount' => 2500,
                'duration' => 1,
                'usage_limit' => 100,
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
            [
                'code' => 'PREMIUM3000',
                'target_plan' => 'premium',
                'discount_amount' => 3000,
                'duration' => 1,
                'usage_limit' => 100,
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
        ];

        foreach ($promoCodes as $promo) {
            PromoCode::updateOrCreate(
                ['code' => $promo['code']],
                $promo
            );
        }
    }
}
