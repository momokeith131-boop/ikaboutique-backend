<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'points_per_1000',
        'points_to_cash_rate',
        'is_active',
    ];

    protected $casts = [
        'points_to_cash_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function calculatePoints($amount): int
    {
        return floor($amount / 1000) * $this->points_per_1000;
    }
}
