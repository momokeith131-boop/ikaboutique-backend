<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_id',
        'points',
        'type',
        'reference',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public static function getTotalPoints($userId, $shopId): int
    {
        return self::where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->sum('points');
    }

    public static function earnPoints($userId, $shopId, $points, $reference = null, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'shop_id' => $shopId,
            'points' => $points,
            'type' => 'earned',
            'reference' => $reference,
            'description' => $description ?? 'Points gagnés',
        ]);
    }

    public static function usePoints($userId, $shopId, $points, $reference = null, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'shop_id' => $shopId,
            'points' => -$points,
            'type' => 'used',
            'reference' => $reference,
            'description' => $description ?? 'Points utilisés',
        ]);
    }
}
