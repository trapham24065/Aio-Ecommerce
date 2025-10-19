<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;

final class SupplierProcessor implements ProcessorInterface
{
    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof StoreSupplierRequest) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['nullable', 'string'],
                'status' => ['required', 'boolean'],
            ];

            if ($operation->getMethod() === 'POST') {
                $rules['code'][] = Rule::unique('suppliers');
                if (!empty($requestData['email'])) {
                    $rules['email'][] = Rule::unique('suppliers');
                }
            } else {
                $supplierId = $uriVariables['id'];
                $rules['code'][] = Rule::unique('suppliers')->ignore($supplierId);
                if (!empty($requestData['email'])) {
                    $rules['email'][] = Rule::unique('suppliers')->ignore($supplierId);
                }
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
                $model = new Supplier($validated);
            } else {
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('Supplier not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }
}
