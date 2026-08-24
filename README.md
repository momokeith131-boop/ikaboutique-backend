# IKABOUTIQUE Backend API V2

🚀 **Backend API Laravel pour l'application mobile IKABOUTIQUE (iOS & Android via Flutter)**

## 📋 Vue d'ensemble

Ce projet est le cœur d'IKABOUTIQUE V2, une plateforme e-commerce SaaS multi-vendeurs construite à partir de zéro en Laravel.

### Architecture
```
                  IKABOUTIQUE API
                       │
        ┌──────────────┼──────────────┐
        │              │              │
     Android          iOS            Web
      (Flutter)     (Flutter)    (Future)
        │              │              │
        └──────────────┼──────────────┘
                       │
                  Base de données
                       │
        ┌──────────────┼──────────────┐
        │              │              │
     Produits      Commandes      Clients
```

## 🛠 Stack Technologique

- **Framework**: Laravel 11
- **Authentification**: JWT (JSON Web Tokens)
- **Base de données**: MySQL
- **Validation**: Laravel Validation
- **API**: REST API

## 📦 Installation

### Prérequis
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (optionnel)

### Setup

1. **Cloner le repository**
   ```bash
   git clone https://github.com/momokeith131-boop/ikaboutique-backend.git
   cd ikaboutique-backend
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurer la base de données**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ikaboutique_v2
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Exécuter les migrations**
   ```bash
   php artisan migrate
   ```

6. **Lancer le serveur**
   ```bash
   php artisan serve
   ```

   L'API sera accessible à : `http://localhost:8000/api/v1`

## 📚 Documentation API

### Authentification

#### Register (Inscription)
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+223123456789",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer" // or "seller"
}
```

**Réponse (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### Login (Connexion)
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Réponse (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

#### Get Current User
```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

## 📊 Modèles de Données

### Users (Utilisateurs)
- Customers (Clients)
- Sellers (Vendeurs)
- Admins

### Shops (Boutiques)
- Une boutique par vendeur
- Informations boutique (nom, logo, adresse, etc.)

### Products (Produits)
- Catalogue complet
- Attributs et variations
- Images produits
- Stock
- Avis clients

### Orders (Commandes)
- Commandes clients
- Statuts (pending, confirmed, processing, shipped, delivered)
- Panier et articles

### Payments (Paiements)
- Transactions
- Multiples méthodes de paiement
- Réconciliation

### Commissions & Referrals (Commissions et Parrainage)
- Système de parrainage
- Commissions automatiques
- Suivi des retraits

### Notifications
- Notifications push
- Historique notifications
- Abonnements notifications

### Expenses (Dépenses)
- Suivi comptable
- Catégories de dépenses
- Reçus

## 🔐 Authentification JWT

Tous les endpoints protégés nécessitent un token JWT dans le header :

```http
Authorization: Bearer {token}
```

Le token est valide pour **60 minutes** par défaut.

## 📝 Variables d'Environnement

```env
# Application
APP_NAME=IKABOUTIQUE
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ikaboutique_v2
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=your-secret-key-here
JWT_ALGORITHM=HS256
JWT_TTL=60

# Payment Gateway
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
```

## 🚀 Déploiement

### Production

1. Cloner sur le serveur
2. Configurer `.env` pour production
3. Exécuter `composer install --no-dev`
4. Exécuter `php artisan config:cache`
5. Exécuter `php artisan route:cache`
6. Exécuter `php artisan view:cache`
7. Configurer le web server (Nginx/Apache)
8. Configurer SSL/HTTPS

## 📖 Documentation Complète

Voir le dossier `docs/` pour :
- Architecture détaillée
- Spécification API complète
- Modèles de données
- Workflows métier

## 🐛 Issues et Support

Pour signaler un bug ou demander une fonctionnalité :
https://github.com/momokeith131-boop/ikaboutique-backend/issues

## 📜 Licence

MIT License - voir LICENSE file

## 👥 Équipe

- **Backend**: momokeith131-boop
- **Frontend Flutter**: (votre ami)

---

**Status**: 🚧 En développement

**Dernière mise à jour**: 2024
