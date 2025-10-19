<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;

final class ProductProcessor implements ProcessorInterface
{
    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof StoreProductRequest) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            $rules = [
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

            if ($operation->getMethod() === 'POST') {
                $rules['sku'][] = Rule::unique('products');
                $rules['name'][] = Rule::unique('products')->where(function ($query) use ($requestData) {
                    return $query->where('category_id', $requestData['category_id'] ?? null);
                });
            } else {
                $productId = $uriVariables['id'];
                $rules['sku'][] = Rule::unique('products')->ignore($productId);
                $rules['name'][] = Rule::unique('products')->ignore($productId)->where(function ($query) use ($requestData) {
                    return $query->where('category_id', $requestData['category_id'] ?? null);
                });
            }

            $validator = \Validator::make($requestData, $rules);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $violations = [];
                $detailMessages = [];

                foreach ($errors->toArray() as $field => $messages) {
                    foreach ($messages as $message) {
                        $violations[] = [
                            'propertyPath' => $field,
                            'message' => $message,
                        ];
                        $detailMessages[] = "{$field}: {$message}";
                    }
                }

                $errorResponse = [
                    'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                    'title' => 'An error occurred',
                    'detail' => 'Validation errors: ' . implode('; ', $detailMessages),
                    'violations' => $violations,
                    'status' => 422,
                ];

                return new JsonResponse($errorResponse, 422);
            }

            $validated = $validator->validated();

            if (isset($validated['status'])) {
                $validated['status'] = filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN);
            }

            if ($operation->getMethod() === 'POST') {
                $model = new Product($validated);
            } else {
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('Product not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }
}