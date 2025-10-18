<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('suppliers')->ignore($supplierId),
            ],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('suppliers')->ignore($supplierId),
            ],
            'home_url' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'boolean'],
        ];
    }
}

