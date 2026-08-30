<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Statistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'event_type',
        'session_id',
        'ip_address',
        'user_agent',
        'referrer',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public static function track(string $eventType, array $data = [])
    {
        return self::create([
            'shop_id' => $data['shop_id'] ?? null,
            'event_type' => $eventType,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public static function getStats($shopId = null)
    {
        $query = self::query();
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        return [
            'visits' => $query->where('event_type', 'visit')->count(),
            'today_visits' => $query->where('event_type', 'visit')
                ->whereDate('created_at', today())
                ->count(),
            'unique_visitors' => $query->where('event_type', 'visit')
                ->distinct('session_id')
                ->count('session_id'),
            'product_views' => $query->where('event_type', 'view_product')->count(),
            'add_to_cart' => $query->where('event_type', 'add_to_cart')->count(),
            'checkouts' => $query->where('event_type', 'checkout')->count(),
            'orders' => $query->where('event_type', 'order')->count(),
            'payments' => $query->where('event_type', 'payment')->count(),
            'revenue' => Order::where('shop_id', $shopId)->sum('total') ?? 0,
        ];
    }
}
