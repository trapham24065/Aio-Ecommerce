<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
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
        $brandId = $this->route('id');

        return [
            'name'   => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands')->ignore($brandId),
            ],
            'code'   => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands')->ignore($brandId),
            ],
            'status' => ['required', 'boolean'], // Yêu cầu là true/false hoặc 1/0
        ];
    }

}
