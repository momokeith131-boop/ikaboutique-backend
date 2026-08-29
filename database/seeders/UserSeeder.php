<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@ikaboutique.com',
            'phone' => '+223123456789',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create sample customers
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Customer $i",
                'email' => "customer$i@example.com",
                'phone' => "+22312345678$i",
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        // Create sample sellers
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Seller $i",
                'email' => "seller$i@example.com",
                'phone' => "+22322345678$i",
                'password' => Hash::make('password123'),
                'role' => 'seller',
                'is_active' => true,
            ]);
        }
    }
}
