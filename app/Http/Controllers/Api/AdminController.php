<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AdminController
{
    // Vérifier que l'utilisateur est Super Admin
    private function checkAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(Response::HTTP_FORBIDDEN, 'Accès réservé au Super Admin');
        }
    }

    // Dashboard principal
    public function dashboard()
    {
        $this->checkAdmin();

        $totalUsers = User::count();
        $totalMerchants = User::where('role', 'seller')->count();
        $totalShops = Shop::count();
        $totalOrders = Order::count();
        $totalPayments = Payment::count();
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        $activeSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '>', now())
            ->count();

        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '<', now())
            ->count();

        $trials = Subscription::where('trial_ends_at', '>', now())->count();

        $newUsers = User::where('created_at', '>=', now()->subDays(7))->count();
        $newShops = Shop::where('created_at', '>=', now()->subDays(7))->count();

        return response()->json([
            'total_users' => $totalUsers,
            'total_merchants' => $totalMerchants,
            'total_shops' => $totalShops,
            'total_orders' => $totalOrders,
            'total_payments' => $totalPayments,
            'total_revenue' => $totalRevenue,
            'active_subscriptions' => $activeSubscriptions,
            'expired_subscriptions' => $expiredSubscriptions,
            'trials' => $trials,
            'new_users' => $newUsers,
            'new_shops' => $newShops,
        ]);
    }

    // Liste des utilisateurs
    public function users(Request $request)
    {
        $this->checkAdmin();

        $query = User::with('shop');

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    // Détail d'un utilisateur
    public function showUser($id)
    {
        $this->checkAdmin();

        $user = User::with(['shop', 'subscriptions'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($user);
    }

    // Liste des boutiques
    public function shops(Request $request)
    {
        $this->checkAdmin();

        $query = Shop::with(['user', 'subscriptions']);

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $shops = $query->paginate($perPage);

        return response()->json($shops);
    }

    // Liste des abonnements
    public function subscriptions(Request $request)
    {
        $this->checkAdmin();

        $query = Subscription::with(['user', 'plan']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $subscriptions = $query->paginate($perPage);

        return response()->json($subscriptions);
    }

    // Liste des paiements
    public function payments(Request $request)
    {
        $this->checkAdmin();

        $query = Payment::with(['user', 'shop']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $payments = $query->paginate($perPage);

        return response()->json($payments);
    }

    // Statistiques avancées
    public function stats()
    {
        $this->checkAdmin();

        // Statistiques par plan
        $plans = Plan::all()->map(function ($plan) {
            return [
                'name' => $plan->display_name,
                'subscriptions' => Subscription::where('plan_id', $plan->id)
                    ->where('status', 'active')
                    ->count(),
                'revenue' => Payment::whereHas('subscription', function ($query) use ($plan) {
                    $query->where('plan_id', $plan->id);
                })->sum('amount'),
            ];
        });

        // Statistiques mensuelles
        $monthlyStats = [
            'orders' => Order::whereMonth('created_at', now()->month)->count(),
            'revenue' => Payment::whereMonth('created_at', now()->month)
                ->where('status', 'completed')
                ->sum('amount'),
            'new_users' => User::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'plans' => $plans,
            'monthly' => $monthlyStats,
        ]);
    }

    // Modifier un utilisateur
    public function updateUser(Request $request, $id)
    {
        $this->checkAdmin();

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|unique:users,phone,' . $id,
            'role' => 'sometimes|in:customer,seller,admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user,
        ]);
    }

    // Suspendre un commerçant
    public function suspendMerchant($id)
    {
        $this->checkAdmin();

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user->is_active = false;
        $user->save();

        // Archiver sa boutique
        $shop = Shop::where('user_id', $id)->first();
        if ($shop) {
            $shop->status = 'archived';
            $shop->is_active = false;
            $shop->save();
        }

        return response()->json([
            'message' => 'Commerçant suspendu avec succès',
            'user' => $user,
        ]);
    }

    // Réactiver un commerçant
    public function reactivateMerchant($id)
    {
        $this->checkAdmin();

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user->is_active = true;
        $user->save();

        // Réactiver sa boutique
        $shop = Shop::where('user_id', $id)->first();
        if ($shop) {
            $shop->status = 'active';
            $shop->is_active = true;
            $shop->save();
        }

        return response()->json([
            'message' => 'Commerçant réactivé avec succès',
            'user' => $user,
        ]);
    }
}
