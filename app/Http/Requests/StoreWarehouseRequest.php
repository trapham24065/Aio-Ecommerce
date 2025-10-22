<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $warehouseId = $this->route('id');

        return [
            'name'    => [
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses')->ignore($warehouseId),
            ],
            'code'    => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses')->ignore($warehouseId),
            ],
            'street'  => ['nullable', 'string', 'max:300'],
            'city'    => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'status'  => ['required', 'boolean'],
        ];
    }

}
