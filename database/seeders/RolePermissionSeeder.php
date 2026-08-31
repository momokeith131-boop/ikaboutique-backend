<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les permissions
        $permissions = [
            // Produits
            ['name' => 'products.view', 'display_name' => 'Voir les produits', 'group' => 'produits'],
            ['name' => 'products.create', 'display_name' => 'Créer des produits', 'group' => 'produits'],
            ['name' => 'products.update', 'display_name' => 'Modifier des produits', 'group' => 'produits'],
            ['name' => 'products.delete', 'display_name' => 'Supprimer des produits', 'group' => 'produits'],

            // Commandes
            ['name' => 'orders.view', 'display_name' => 'Voir les commandes', 'group' => 'commandes'],
            ['name' => 'orders.update', 'display_name' => 'Modifier les commandes', 'group' => 'commandes'],

            // Utilisateurs
            ['name' => 'users.view', 'display_name' => 'Voir les utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'users.create', 'display_name' => 'Créer des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'users.update', 'display_name' => 'Modifier des utilisateurs', 'group' => 'utilisateurs'],
            ['name' => 'users.delete', 'display_name' => 'Supprimer des utilisateurs', 'group' => 'utilisateurs'],

            // Administrateurs
            ['name' => 'admins.view', 'display_name' => 'Voir les administrateurs', 'group' => 'administrateurs'],
            ['name' => 'admins.create', 'display_name' => 'Créer des administrateurs', 'group' => 'administrateurs'],

            // Boutiques
            ['name' => 'shops.view', 'display_name' => 'Voir les boutiques', 'group' => 'boutiques'],
            ['name' => 'shops.update', 'display_name' => 'Modifier les boutiques', 'group' => 'boutiques'],

            // Abonnements
            ['name' => 'subscriptions.view', 'display_name' => 'Voir les abonnements', 'group' => 'abonnements'],
            ['name' => 'subscriptions.update', 'display_name' => 'Modifier les abonnements', 'group' => 'abonnements'],

            // Paramètres
            ['name' => 'settings.view', 'display_name' => 'Voir les paramètres', 'group' => 'paramètres'],
            ['name' => 'settings.update', 'display_name' => 'Modifier les paramètres', 'group' => 'paramètres'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Créer les rôles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrateur',
                'description' => 'Accès total à toutes les fonctionnalités',
                'is_default' => false,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Accès à la gestion de la plateforme',
                'is_default' => false,
            ],
            [
                'name' => 'merchant',
                'display_name' => 'Commerçant',
                'description' => 'Accès à la gestion de sa boutique',
                'is_default' => true,
            ],
            [
                'name' => 'support',
                'display_name' => 'Support',
                'description' => 'Accès au support client',
                'is_default' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Attribuer des permissions aux rôles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $merchant = Role::where('name', 'merchant')->first();
        $support = Role::where('name', 'support')->first();

        // Super Admin : toutes les permissions
        $allPermissions = Permission::all();
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // Admin : presque toutes
        $adminPermissions = Permission::whereIn('name', [
            'products.view', 'orders.view', 'orders.update',
            'users.view', 'users.update', 'shops.view', 'shops.update',
            'subscriptions.view', 'subscriptions.update',
        ])->get();
        $admin->permissions()->sync($adminPermissions->pluck('id'));

        // Merchant : produits et commandes
        $merchantPermissions = Permission::whereIn('name', [
            'products.view', 'products.create', 'products.update',
            'orders.view', 'shops.view',
        ])->get();
        $merchant->permissions()->sync($merchantPermissions->pluck('id'));

        // Support : commandes
        $supportPermissions = Permission::whereIn('name', [
            'orders.view', 'orders.update',
        ])->get();
        $support->permissions()->sync($supportPermissions->pluck('id'));
    }
}
