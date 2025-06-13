<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:100'
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Товар не вказано',
            'product_id.exists' => 'Товар не знайдено',
            'quantity.integer' => 'Кількість повинна бути цілим числом',
            'quantity.min' => 'Мінімальна кількість 1',
            'quantity.max' => 'Максимальна кількість 100',
        ];
    }
} 