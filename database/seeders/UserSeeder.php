<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Тестовий користувач',
                'password' => Hash::make('password'),
                'email_verified_at' => now()
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@shopua.com'],
            [
                'name' => 'Адмін',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now()
            ]
        );
    }
}
