<?php

namespace App\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\ApiPlatform\State\InventoryProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/inventories'),
        new Get(uriTemplate: '/inventories/{id}'),
        new Post(uriTemplate: '/inventories', processor: InventoryProcessor::class),
        new Put(uriTemplate: '/inventories/{id}', processor: InventoryProcessor::class),
        new Delete(uriTemplate: '/inventories/{id}'),
    ],
    routePrefix: '/api',
    normalizationContext: ['groups' => ['inventory:read', 'inventory:detail:read', 'warehouse:detail:read']],
    security: "is_granted('ROLE_USER')"
)]
class Inventory extends Model
{

    protected $table = 'inventory';

    protected $fillable = ['warehouse_id', 'product_variant_sku', 'quantity'];

    protected $visible = ['id', 'warehouse_id', 'product_variant_sku', 'quantity'];

    #[Groups(['inventory:read', 'inventory:detail:read', 'warehouse:detail:read', 'receipt:detail:read',])]
    protected $id;

    #[Groups(['inventory:read', 'inventory:detail:read', 'warehouse:detail:read', 'receipt:detail:read',])]
    protected $warehouse_id;

    #[Groups(['inventory:read', 'inventory:detail:read', 'warehouse:detail:read', 'receipt:detail:read',])]
    protected $product_variant_sku;

    #[Groups(['inventory:read', 'inventory:detail:read', 'warehouse:detail:read', 'receipt:detail:read',])]
    protected $quantity;

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_sku', 'sku');
    }

}
