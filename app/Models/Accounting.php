<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accounting extends Model
{
    use HasFactory;

    protected $table = 'accounting';

    protected $fillable = [
        'shop_id',
        'type',
        'category',
        'amount',
        'description',
        'reference',
        'transaction_date',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'metadata' => 'array',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public static function revenue($shopId, $amount, $category, $description = null, $reference = null)
    {
        return self::create([
            'shop_id' => $shopId,
            'type' => 'revenue',
            'category' => $category,
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
            'transaction_date' => now(),
            'status' => 'completed',
        ]);
    }

    public static function expense($shopId, $amount, $category, $description = null, $reference = null)
    {
        return self::create([
            'shop_id' => $shopId,
            'type' => 'expense',
            'category' => $category,
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
            'transaction_date' => now(),
            'status' => 'pending',
        ]);
    }

    public static function getSummary($shopId)
    {
        $totalRevenue = self::where('shop_id', $shopId)
            ->where('type', 'revenue')
            ->where('status', 'completed')
            ->sum('amount');

        $totalExpenses = self::where('shop_id', $shopId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingRevenue = self::where('shop_id', $shopId)
            ->where('type', 'revenue')
            ->where('status', 'pending')
            ->sum('amount');

        $pendingExpenses = self::where('shop_id', $shopId)
            ->where('type', 'expense')
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'profit' => $totalRevenue - $totalExpenses,
            'pending_revenue' => $pendingRevenue,
            'pending_expenses' => $pendingExpenses,
        ];
    }
}
