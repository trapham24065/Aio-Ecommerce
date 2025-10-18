<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use InvalidArgumentException;

final class OrderProcessor implements ProcessorInterface
{
    public function __construct(private PersistProcessor $persist)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof StoreOrderRequest) {
            $request = $context['request'] ?? null;
            $requestData = $request ? $request->all() : [];

            $rules = [
                'customer_id' => ['required', 'exists:customers,id'],
                'status' => ['required', 'in:pending,processing,delivered,cancelled'],
                'currency' => ['required', 'string', 'max:3'],
                'subtotal' => ['required', 'numeric', 'min:0'],
                'shipping_fee' => ['nullable', 'numeric', 'min:0'],
                'tax_amount' => ['nullable', 'numeric', 'min:0'],
                'discount_amount' => ['nullable', 'numeric', 'min:0'],
                'grand_total' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string'],
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
                $model = new Order($validated);
            } else {
                $model = $context['previous_data'] ?? null;
                if (!$model) {
                    throw new InvalidArgumentException('Order not found in previous_data context.');
                }
                $model->fill($validated);
            }

            return $this->persist->process($model, $operation, $uriVariables, $context);
        }

        return $this->persist->process($data, $operation, $uriVariables, $context);
    }
}