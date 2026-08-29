<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    // Vérifier si l'utilisateur peut voir la liste des produits
    public function viewAny(User $user): bool
    {
        return true; // Tout le monde peut voir la liste
    }

    // Vérifier si l'utilisateur peut voir un produit spécifique
    public function view(User $user, Product $product): bool
    {
        return true; // Tout le monde peut voir un produit
    }

    // Vérifier si l'utilisateur peut créer un produit
    public function create(User $user): bool
    {
        // Seuls les vendeurs et les admins peuvent créer des produits
        return in_array($user->role, ['seller', 'admin']);
    }

    // Vérifier si l'utilisateur peut modifier un produit
    public function update(User $user, Product $product): bool
    {
        // L'utilisateur doit être vendeur ou admin ET le propriétaire du produit
        if (!in_array($user->role, ['seller', 'admin'])) {
            return false;
        }

        // Si c'est un admin, il peut tout modifier
        if ($user->role === 'admin') {
            return true;
        }

        // Si c'est un vendeur, il ne peut modifier que ses propres produits
        return $user->id === $product->shop->user_id;
    }

    // Vérifier si l'utilisateur peut supprimer un produit
    public function delete(User $user, Product $product): bool
    {
        // Mêmes règles que pour la modification
        if (!in_array($user->role, ['seller', 'admin'])) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $user->id === $product->shop->user_id;
    }
}
