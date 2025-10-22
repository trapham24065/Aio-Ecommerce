<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Laravel\Eloquent\State\PersistProcessor;
use App\Models\GoodsReceipt;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class GoodsReceiptProcessor implements ProcessorInterface
{

    public function __construct(
        private PersistProcessor $persist
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $request = $context['request'] ?? null;
        $requestData = $request ? $request->all() : [];

        // Validation rules
        $rules = [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_sku' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];

        $validator = Validator::make($requestData, $rules);
        if ($validator->fails()) {
            throw new \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException(
                'Validation errors: ' . implode('; ', $validator->errors()->all())
            );
        }

        $validated = $validator->validated();

        if ($operation->getMethod() === 'POST') {
            return DB::transaction(function () use ($validated, $operation, $uriVariables, $context) {
                // Tạo goods receipt
                $receipt = new GoodsReceipt([
                    'warehouse_id' => $validated['warehouse_id'],
                    'supplier_id' => $validated['supplier_id'],
                    'receipt_date' => $validated['receipt_date'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $result = $this->persist->process($receipt, $operation, $uriVariables, $context);

                // Tạo items và cập nhật inventory
                foreach ($validated['items'] as $itemData) {
                    // Tạo goods receipt item
                    $receipt->items()->create([
                        'product_variant_sku' => $itemData['product_variant_sku'],
                        'quantity' => $itemData['quantity'],
                    ]);

                    // Cập nhật inventory
                    $inventory = Inventory::firstOrCreate(
                        [
                            'warehouse_id' => $receipt->warehouse_id,
                            'product_variant_sku' => $itemData['product_variant_sku']
                        ],
                        ['quantity' => 0]
                    );
                    $inventory->quantity += $itemData['quantity'];
                    $inventory->save();

                    // Tạo inventory transaction
                    InventoryTransaction::create([
                        'warehouse_id' => $receipt->warehouse_id,
                        'product_variant_sku' => $itemData['product_variant_sku'],
                        'type' => 'IN',
                        'quantity_change' => $itemData['quantity'],
                        'reference_id' => $receipt->id,
                        'reference_type' => GoodsReceipt::class,
                        'user_id' => $receipt->user_id,
                        'notes' => 'Goods Receipt: ' . $receipt->code,
                    ]);
                }

                return $result;
            });
        }

        // Handle PUT/PATCH operations
        $model = $context['previous_data'] ?? null;
        if (!$model) {
            throw new \InvalidArgumentException('GoodsReceipt not found in previous_data context.');
        }
        $model->fill($validated);

        return $this->persist->process($model, $operation, $uriVariables, $context);
    }

}

