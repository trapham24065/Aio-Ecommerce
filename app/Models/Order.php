<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Http\Requests\StoreOrderRequest;
use App\ApiPlatform\State\OrderProcessor;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            input: StoreOrderRequest::class,
            processor: OrderProcessor::class
        ),
        new Get(),
        new Put(
            input: StoreOrderRequest::class,
            processor: OrderProcessor::class
        ),
        new Delete(),
    ],
    security: "is_granted('ROLE_USER')"
)]
class Order extends Model
{

    use HasFactory;

    protected $fillable
    = [
        'customer_id',
        'order_code',
        'status',
        'currency',
        'subtotal',
        'shipping_fee',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->order_code = 'DH-' . now()->year . '-' . str_pad(self::count() + 1, 6, '0', STR_PAD_LEFT);
        });
    }
}
