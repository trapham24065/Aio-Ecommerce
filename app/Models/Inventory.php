<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Http\Requests\StoreInventoryRequest;
use App\ApiPlatform\State\InventoryProcessor;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            input: StoreInventoryRequest::class,
            processor: InventoryProcessor::class
        ),
        new Get(),
        new Put(
            input: StoreInventoryRequest::class,
            processor: InventoryProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
class Inventory extends Model
{

    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = ['warehouse_id', 'product_variant_sku', 'quantity'];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_sku', 'sku');
    }

}

