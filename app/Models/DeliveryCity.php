<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_zone_id',
        'name',
        'region',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }
}
