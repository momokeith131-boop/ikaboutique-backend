<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'gateway',
        'transaction_id',
        'reference',
        'amount',
        'status',
        'response_data',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_data' => 'json',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
