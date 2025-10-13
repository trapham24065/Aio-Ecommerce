<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            $receipt->code = 'GRN-'.now()->year.'-'.str_pad(self::count() + 1, 5, '0', STR_PAD_LEFT);
            if (auth()->check()) {
                $receipt->user_id = auth()->id();
            }
        });
    }

}
