<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Бездротові навушники Sony',
                'description' => 'Якісні бездротові навушники з активним шумопоглинанням та тривалим часом роботи.',
                'price' => 2499.00,
                'category' => 'electronics',
                'stock' => 25,
                'image' => 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Розумний годинник Apple Watch',
                'description' => 'Сучасний розумний годинник з фітнес-трекером та великою кількістю корисних функцій.',
                'price' => 8999.00,
                'category' => 'electronics',
                'stock' => 15,
                'image' => 'https://images.pexels.com/photos/437037/pexels-photo-437037.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Ноутбук MacBook Air',
                'description' => 'Потужний та легкий ноутбук для роботи та навчання з процесором M1.',
                'price' => 32999.00,
                'category' => 'electronics',
                'stock' => 8,
                'image' => 'https://images.pexels.com/photos/18105/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Стильна куртка',
                'description' => 'Модна та комфортна куртка для прохолодної погоди.',
                'price' => 1899.00,
                'category' => 'clothing',
                'stock' => 30,
                'image' => 'https://images.pexels.com/photos/1040945/pexels-photo-1040945.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Кросівки Nike',
                'description' => 'Зручні спортивні кросівки для активного способу життя.',
                'price' => 3299.00,
                'category' => 'shoes',
                'stock' => 45,
                'image' => 'https://images.pexels.com/photos/2529148/pexels-photo-2529148.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Кавоварка Delonghi',
                'description' => 'Автоматична кавоварка для приготування ідеальної кави вдома.',
                'price' => 4599.00,
                'category' => 'home',
                'stock' => 12,
                'image' => 'https://images.pexels.com/photos/324028/pexels-photo-324028.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Настільна лампа LED',
                'description' => 'Сучасна LED лампа з регулюванням яскравості та температури світла.',
                'price' => 899.00,
                'category' => 'home',
                'stock' => 20,
                'image' => 'https://images.pexels.com/photos/1112598/pexels-photo-1112598.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ],
            [
                'name' => 'Смартфон iPhone 14',
                'description' => 'Найновіший смартфон від Apple з покращеною камерою та продуктивністю.',
                'price' => 24999.00,
                'category' => 'electronics',
                'stock' => 18,
                'image' => 'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg?auto=compress&cs=tinysrgb&w=400',
                'is_active' => true,
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
