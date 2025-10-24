<?php

namespace App\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Models\GoodsReceipt;
use ApiPlatform\Laravel\Eloquent\State\CollectionProvider;
use ApiPlatform\Laravel\Eloquent\State\ItemProvider;

final class GoodsReceiptProvider implements ProviderInterface
{

    public function __construct(
        private CollectionProvider $collectionProvider,
        private ItemProvider $itemProvider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof \ApiPlatform\Metadata\GetCollection) {
            $receipts = $this->collectionProvider->provide($operation, $uriVariables, $context);

            if ($receipts instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $receipts->getCollection()->load(['items', 'warehouse', 'supplier', 'user']);
            }

            return $receipts;
        }

        if ($operation instanceof \ApiPlatform\Metadata\Get) {
            $receipt = $this->itemProvider->provide($operation, $uriVariables, $context);

            if ($receipt instanceof GoodsReceipt) {
                $receipt->load(['items.productVariant', 'warehouse', 'supplier', 'user']);
            }

            return $receipt;
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }

}
