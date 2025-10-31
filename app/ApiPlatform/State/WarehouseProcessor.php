<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Http\Requests\StoreWarehouseRequest;
use App\Models\Warehouse;
use \InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use App\Dto\WarehouseInput;

final class WarehouseProcessor implements ProcessorInterface
{

    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof WarehouseInput) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            // Auto-generate code from name if not provided
            if (!empty($requestData['name']) && empty($requestData['code'])) {
                $name = $requestData['name'];
                $sanitized = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name);
                $baseCode = \Str::slug($sanitized);
                $baseCode = \Str::limit($baseCode, 50, '');
                $baseCode = trim($baseCode, '-');
                $baseCode = strtoupper($baseCode);

                $finalCode = $baseCode;
                $counter = 1;
                $recordId = $uriVariables['id'] ?? null;

                while (
                Warehouse::where('code', $finalCode)
                    ->when($recordId, fn($query) => $query->where('id', '!=', $recordId))
                    ->exists()
                ) {
                    $counter++;
                    $finalCode = $baseCode.'-'.$counter;
                }

                $requestData['code'] = $finalCode;
            }

            $rules = [
                'name'        => ['required', 'string', 'max:100'],
                'code'        => ['required', 'string', 'max:50'],
                'street'      => ['nullable', 'string', 'max:300'],
                'city'        => ['required', 'string', 'max:100'],
                'state'       => ['nullable', 'string', 'max:100'],
                'postalCode'  => ['nullable', 'string', 'max:100'],
                'postal_code' => ['nullable', 'string', 'max:100'],
                'country'     => ['required', 'string', 'max:100'],
                'status'      => ['nullable', 'boolean'],
            ];

            if ($operation->getMethod() === 'POST') {
                $rules['name'][] = Rule::unique('warehouses');
                $rules['code'][] = Rule::unique('warehouses');
            } else {
                $warehouseId = $uriVariables['id'];
                $rules['name'][] = Rule::unique('warehouses')->ignore($warehouseId);
                $rules['code'][] = Rule::unique('warehouses')->ignore($warehouseId);
            }
            if (!array_key_exists('status', $requestData) || $requestData['status'] === ''
                || $requestData['status'] === null
            ) {
                $requestData['status'] = true;
            }
            $validator = \Validator::make($requestData, $rules);

            if ($validator->fails()) {
                return ValidationErrorProvider::toJsonResponse($validator->errors());
            }

            $validated = $validator->validated();

            if (isset($validated['postalCode']) && !isset($validated['postal_code'])) {
                $validated['postal_code'] = $validated['postalCode'];
                unset($validated['postalCode']);
            }

            if (isset($validated['status'])) {
                $validated['status'] = filter_var($validated['status'], FILTER_VALIDATE_BOOLEAN);
            }

            if ($operation->getMethod() === 'POST') {
                $model = new Warehouse($validated);
            } else {
                /** @var \App\Models\Warehouse|null $model */
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('Warehouse not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }

}


