<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'price',
        'prices',
        'currency',
        'features',
        'is_active',
    ];

    protected $casts = [
        'prices' => 'array',
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getMaxProductsAttribute()
    {
        return $this->features['max_products'] ?? 5;
    }

    public function getMaxOrdersAttribute()
    {
        return $this->features['max_orders'] ?? 50;
    }

    public function getHasSupportAttribute()
    {
        return $this->features['support'] ?? false;
    }

    public function getHasAnalyticsAttribute()
    {
        return $this->features['analytics'] ?? false;
    }
}
