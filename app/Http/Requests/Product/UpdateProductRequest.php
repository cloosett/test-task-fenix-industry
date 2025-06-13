<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'nullable|string|url',
            'category' => 'sometimes|string|in:electronics,clothing,shoes,home',
            'stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'Назва товару повинна бути текстом',
            'name.max' => 'Назва товару не може бути довше 255 символів',
            'description.string' => 'Опис товару повинен бути текстом',
            'price.numeric' => 'Ціна повинна бути числом',
            'price.min' => 'Ціна не може бути від\'ємною',
            'image.url' => 'Невірний формат URL зображення',
            'category.string' => 'Категорія повинна бути текстом',
            'category.in' => 'Невірна категорія товару',
            'stock.integer' => 'Кількість повинна бути цілим числом',
            'stock.min' => 'Кількість не може бути від\'ємною',
            'is_active.boolean' => 'Невірне значення для статусу активності',
        ];
    }
} 