<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Dto\BrandInput;
use App\Models\Brand;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;

final class BrandProcessor implements ProcessorInterface
{

    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof BrandInput) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            if (!empty($requestData['name']) && empty($requestData['code'])) {
                $name = $requestData['name'];
                $sanitized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
                $baseCode = \Str::slug($sanitized);
                $baseCode = \Str::limit($baseCode, 100, '');
                $baseCode = trim($baseCode, '-');

                $finalCode = $baseCode;
                $counter = 1;
                $recordId = $uriVariables['id'] ?? null;

                while (
                Brand::where('code', $finalCode)
                    ->when($recordId, fn($query) => $query->where('id', '!=', $recordId))
                    ->exists()
                ) {
                    $counter++;
                    $finalCode = $baseCode.'-'.$counter;
                }

                $requestData['code'] = $finalCode;
            }

            $rules = [
                'name'   => ['required', 'string', 'max:100'],
                'code'   => ['required', 'string', 'max:100'],
                'status' => ['required', 'boolean'],
            ];

            if ($operation->getMethod() === 'POST') {
                $rules['name'][] = Rule::unique('brands');
                $rules['code'][] = Rule::unique('brands');
            } else {
                $brandId = $uriVariables['id'];
                $rules['name'][] = Rule::unique('brands')->ignore($brandId);
                $rules['code'][] = Rule::unique('brands')->ignore($brandId);
            }

            $validator = \Validator::make($requestData, $rules);

            if ($validator->fails()) {
                return ValidationErrorProvider::toJsonResponse($validator->errors());
            }

            $validated = $validator->validated();

            if (isset($validated['status'])) {
                $validated['status'] = filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN);
            }

            if ($operation->getMethod() === 'POST') {
                $model = new Brand($validated);
            } else {
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('Brand not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }

}


