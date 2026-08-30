<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'zone',
        'amount',
        'free_threshold',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'free_threshold' => 'decimal:2',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function getFee($subtotal): float
    {
        if ($this->free_threshold && $subtotal >= $this->free_threshold) {
            return 0;
        }
        return $this->amount;
    }
}
