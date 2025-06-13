<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|in:electronics,clothing,shoes,home',
            'image' => 'required|url'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Назва товару обов\'язкова',
            'description.required' => 'Опис товару обов\'язковий',
            'price.required' => 'Ціна товару обов\'язкова',
            'price.numeric' => 'Ціна повинна бути числом',
            'price.min' => 'Ціна не може бути від\'ємною',
            'stock.required' => 'Кількість товару обов\'язкова',
            'stock.integer' => 'Кількість повинна бути цілим числом',
            'stock.min' => 'Кількість не може бути від\'ємною',
            'category.required' => 'Категорія товару обов\'язкова',
            'category.in' => 'Невірна категорія товару',
            'image.required' => 'Зображення товару обов\'язкове',
            'image.url' => 'Невірний формат URL зображення'
        ];
    }
} 