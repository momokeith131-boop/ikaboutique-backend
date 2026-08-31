<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check';
    protected $description = 'Vérifie les stocks faibles et envoie des alertes';

    public function handle()
    {
        $this->info('🔍 Vérification des stocks...');

        // Trouver les produits en stock faible
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->with('shop.user')
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('✅ Aucun produit en stock faible.');
            return Command::SUCCESS;
        }

        $this->info('⚠️ ' . $lowStockProducts->count() . ' produit(s) en stock faible.');

        foreach ($lowStockProducts as $product) {
            $user = $product->shop->user;

            if ($user) {
                // Créer une notification
                Notification::create([
                    'user_id' => $user->id,
                    'title' => '⚠️ Stock faible',
                    'message' => "Le produit '{$product->name}' a un stock de {$product->stock} unités. Seuil : {$product->low_stock_threshold}.",
                    'type' => 'warning',
                    'link' => '/products/' . $product->id,
                ]);

                $this->line("📧 Notification envoyée à {$user->email} pour '{$product->name}'");
                Log::info('Alerte stock faible envoyée', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'stock' => $product->stock,
                    'user_id' => $user->id,
                ]);
            }
        }

        $this->info('✅ Alertes envoyées avec succès.');
        return Command::SUCCESS;
    }
}
