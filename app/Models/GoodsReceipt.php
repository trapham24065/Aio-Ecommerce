<?php

namespace App\Models;

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
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\ApiPlatform\State\GoodsReceiptProcessor;
use App\ApiPlatform\State\GoodsReceiptProvider;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['receipt:list']],
            provider: GoodsReceiptProvider::class
        ),
        new Post(
            denormalizationContext: ['groups' => ['receipt:write']],
            input: StoreGoodsReceiptRequest::class,
            processor: GoodsReceiptProcessor::class
        ),
        new Get(
            normalizationContext: ['groups' => ['receipt:detail:read']],
            provider: GoodsReceiptProvider::class
        ),
        new Put(
            input: StoreGoodsReceiptRequest::class,
            processor: GoodsReceiptProcessor::class
        ),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['receipt:read']],
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

    #[Groups(['receipt:detail:read', 'receipt:write'])]
    public ?Collection $items;

    public function items(): HasMany
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

    protected static function booted(): void
    {
        static::creating(function (GoodsReceipt $receipt) {
            $receipt->code = 'GRN-' . now()->year . '-' . str_pad(self::count() + 1, 5, '0', STR_PAD_LEFT);
            if (auth()->check()) {
                $receipt->user_id = auth()->id();
            }
        });
    }
}

