<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_variant_sku' => ['required', 'exists:product_variants,sku'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
