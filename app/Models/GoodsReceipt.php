<?php

namespace App\Models;

use App\ApiPlatform\State\GoodsReceiptProvider;
use App\Dto\GoodsReceiptInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ApiPlatform\State\GoodsReceiptProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['receipt:list']],
            provider: GoodsReceiptProvider::class
        ),
        new Get(
            normalizationContext: ['groups' => ['receipt:detail:read']],
            provider: GoodsReceiptProvider::class
        ),
        new Post(
            normalizationContext: ['groups' => ['receipt:detail:read']],
            input: GoodsReceiptInput::class,
            processor: GoodsReceiptProcessor::class
        ),
        new Put(
            denormalizationContext: ['groups' => ['receipt:write']],
            input: GoodsReceiptInput::class,
            processor: GoodsReceiptProcessor::class
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['receipt:detail:read']],
    denormalizationContext: ['groups' => ['receipt:write']],
    security: "is_granted('ROLE_USER')"
)]
class GoodsReceipt extends Model
{

    use HasFactory;

    protected $fillable
        = [
            'warehouse_id',
            'supplier_id',
            'code',
            'notes',
            'receipt_date',
            'user_id',
        ];

    protected $casts
        = [
            'receipt_date' => 'date',
        ];

    protected $with = ['items', 'warehouse', 'supplier', 'user'];

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getId()
    {
        return $this->id;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    #[SerializedName('code')]
    public function getCode()
    {
        return $this->code;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    #[SerializedName('warehouse_id')]
    public function getWarehouseId()
    {
        return $this->getAttribute('warehouse_id');
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    #[SerializedName('supplier_id')]
    public function getSupplierId()
    {
        return $this->getAttribute('supplier_id');
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getReceiptDate(): ?string
    {
        return $this->receipt_date ? $this->receipt_date->format('Y-m-d') : null;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getNotes()
    {
        return $this->notes;
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    public function getStatus()
    {
        return $this->status;
    }

    public function items(): HasMany|GoodsReceipt
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Groups(['receipt:detail:read', 'receipt:list'])]
    #[SerializedName('items')]
    public function getItems()
    {
        $this->loadMissing('items.productVariant');

        return $this->items->map(function ($item) {
            return [
                'id'       => $item->id,
                'sku'      => $item->product_variant_sku ?? optional($item->productVariant)->sku,
                'quantity' => $item->quantity,
            ];
        });
    }

    #[Groups(['receipt:detail:read'])]
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    #[Groups(['receipt:detail:read'])]
    public function getSupplier()
    {
        return $this->supplier;
    }

    #[Groups(['receipt:detail:read'])]
    public function getUser()
    {
        return $this->user;
    }

    protected static function booted(): void
    {
        static::creating(function (GoodsReceipt $receipt) {
            $receipt->code = 'GRN-'.now()->year.'-'.str_pad(self::count() + 1, 5, '0', STR_PAD_LEFT);
            if (auth()->check()) {
                $receipt->user_id = auth()->id();
            }
        });
    }

}
