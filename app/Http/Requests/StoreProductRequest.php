<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:simple,variant'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:100'],
            'sku' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'thumbnail' => ['required', 'string'],
            'base_cost' => ['required', 'numeric', 'min:1', 'max:9999999999999.99'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'flag' => ['nullable', 'integer'],
            'status' => ['required', 'boolean'],
        ];
    }
}