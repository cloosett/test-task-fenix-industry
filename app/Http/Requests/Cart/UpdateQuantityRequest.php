<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuantityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'quantity' => 'required|integer|min:1|max:100'
        ];
    }

    public function messages()
    {
        return [
            'quantity.required' => 'Кількість обов\'язкова',
            'quantity.integer' => 'Кількість повинна бути цілим числом',
            'quantity.min' => 'Мінімальна кількість 1',
            'quantity.max' => 'Максимальна кількість 100',
        ];
    }
} 