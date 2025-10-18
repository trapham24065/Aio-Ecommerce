<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->ignore($categoryId),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->ignore($categoryId),
            ],
            'status' => ['required', 'boolean'],
        ];
    }
}
