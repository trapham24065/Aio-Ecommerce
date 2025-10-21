<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\Rule;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Dto\SupplierInput;
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
        if ($data instanceof SupplierInput) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            // Auto-generate code from name if not provided
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
                    Supplier::where('code', $finalCode)
                        ->when($recordId, fn($query) => $query->where('id', '!=', $recordId))
                        ->exists()
                ) {
                    $counter++;
                    $finalCode = $baseCode.'-'.$counter;
                }

                $requestData['code'] = $finalCode;
            }

            $rules = [
                'name'     => ['required', 'string', 'max:100'],
                'code'     => ['required', 'string', 'max:100'],
                'home_url' => ['nullable', 'url', 'max:255'],
                'status'   => ['required', 'boolean'],
            ];

            if ($operation->getMethod() === 'POST') {
                $rules['name'][] = Rule::unique('suppliers');
                $rules['code'][] = Rule::unique('suppliers');
            } else {
                $supplierId = $uriVariables['id'];
                $rules['name'][] = Rule::unique('suppliers')->ignore($supplierId);
                $rules['code'][] = Rule::unique('suppliers')->ignore($supplierId);
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
                            'message'      => $message,
                        ];
                        $detailMessages[] = "{$field}: {$message}";
                    }
                }

                $errorResponse = [
                    'type'       => 'https://tools.ietf.org/html/rfc2616#section-10',
                    'title'      => 'An error occurred',
                    'detail'     => 'Validation errors: '.implode('; ', $detailMessages),
                    'violations' => $violations,
                    'status'     => 422,
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

