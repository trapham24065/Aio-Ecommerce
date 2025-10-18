<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use InvalidArgumentException;

final class GoodsReceiptProcessor implements ProcessorInterface
{
    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof StoreGoodsReceiptRequest) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            $rules = [
                'warehouse_id' => ['required', 'exists:warehouses,id'],
                'supplier_id' => ['required', 'exists:suppliers,id'],
                'notes' => ['nullable', 'string'],
                'receipt_date' => ['required', 'date'],
            ];

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

                throw new UnprocessableEntityHttpException(json_encode($errorResponse));
            }

            $validated = $validator->validated();

            if ($operation->getMethod() === 'POST') {
                $model = new GoodsReceipt($validated);
            } else {
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('GoodsReceipt not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }
}