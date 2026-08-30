<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'domain',
        'status',
        'is_primary',
        'verified_at',
        'dns_records',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
        'dns_records' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified' || $this->status === 'active';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
