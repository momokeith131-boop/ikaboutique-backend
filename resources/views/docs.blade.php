
<html>
<head>
    <meta charset="UTF-8">
    <title>IKABOUTIQUE API - Documentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0d1117; color: #e6edf3; padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { font-size: 32px; color: #f0f6fc; border-bottom: 2px solid #30363d; padding-bottom: 15px; }
        h2 { font-size: 24px; color: #f0f6fc; margin-top: 40px; margin-bottom: 20px; }
        .info { background: #161b22; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #58a6ff; margin-bottom: 30px; }
        .info code { background: #21262d; padding: 2px 8px; border-radius: 4px; }
        .endpoint { background: #161b22; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #30363d; }
        .endpoint:hover { border-color: #58a6ff; }
        .method { display: inline-block; padding: 4px 12px; border-radius: 6px; font-weight: bold; color: white; font-size: 13px; }
        .get { background: #1f7b4d; }
        .post { background: #1a6e8a; }
        .put { background: #9e6a03; }
        .delete { background: #b62324; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 10px; }
        .badge-public { background: #1f7b4d; color: #fff; }
        .badge-private { background: #b62324; color: #fff; }
        .path { font-family: 'Courier New', monospace; color: #f0f6fc; font-size: 14px; }
        pre { background: #0d1117; padding: 15px; border-radius: 8px; overflow-x: auto; margin-top: 10px; border: 1px solid #30363d; font-size: 13px; }
        code { font-family: 'Courier New', monospace; }
        .example { margin-top: 10px; }
        .example-text { color: #8b949e; font-size: 13px; }
        .badge-auth { background: #da3633; color: white; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 IKABOUTIQUE API V2</h1>
        <p style="color: #8b949e; margin-bottom: 20px;">Documentation complète de l'API e-commerce IKABOUTIQUE</p>

        <div class="info">
            <strong>🌐 Base URL :</strong> <code>http://127.0.0.1:8000/api/v1</code><br>
            <strong>🔑 Test Account :</strong> <code>vendeur@test.com</code> / <code>password123</code><br>
            <strong>📱 Test Phone :</strong> <code>+22378088551</code>
        </div>

        <h2>🔐 Authentification</h2>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/register</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Créer un nouveau compte utilisateur</p>
            <pre>{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+22378088551",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "customer"
}</pre>
            <div class="example-text">📌 <strong>Rôle :</strong> customer ou seller</div>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/login</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Se connecter et obtenir un token JWT</p>
            <pre>{
    "email": "vendeur@test.com",
    "password": "password123"
}</pre>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/auth/me</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Obtenir les informations de l'utilisateur connecté</p>
            <pre>Authorization: Bearer {{token}}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/logout</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Déconnecter l'utilisateur et invalider le token</p>
            <pre>Authorization: Bearer {{token}}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/refresh</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Rafraîchir le token JWT</p>
            <pre>Authorization: Bearer {{token}}</pre>
        </div>

        <h2>📦 Produits</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/products</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Lister tous les produits</p>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/products/{id}</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Voir les détails d'un produit</p>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/products</span>
            <span class="badge badge-private">Protégé</span>
            <span class="badge-auth">Seller/Admin</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Créer un nouveau produit</p>
            <pre>{
    "name": "Mon produit",
    "slug": "mon-produit",
    "description": "Description",
    "price": 99.99,
    "stock": 10,
    "category_id": 1,
    "shop_id": 1
}</pre>
        </div>

        <div class="endpoint">
            <span class="method put">PUT</span> <span class="path">/products/{id}</span>
            <span class="badge badge-private">Protégé</span>
            <span class="badge-auth">Seller/Admin</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Modifier un produit existant</p>
        </div>

        <div class="endpoint">
            <span class="method delete">DELETE</span> <span class="path">/products/{id}</span>
            <span class="badge badge-private">Protégé</span>
            <span class="badge-auth">Seller/Admin</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Supprimer un produit</p>
        </div>

        <h2>🛒 Panier</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/cart</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Voir le contenu du panier</p>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/cart/add</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Ajouter un produit au panier</p>
            <pre>{
    "product_id": 1,
    "quantity": 2
}</pre>
        </div>

        <div class="endpoint">
            <span class="method delete">DELETE</span> <span class="path">/cart/clear</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Vider le panier</p>
        </div>

        <h2>📦 Commandes</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/orders</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Lister les commandes de l'utilisateur</p>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/orders</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Créer une nouvelle commande</p>
            <pre>{
    "shipping_address": "123 Rue Test",
    "billing_address": "123 Rue Test",
    "payment_method": "card"
}</pre>
        </div>

        <h2>💳 Paiements</h2>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/payments/initiate</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Initier un paiement</p>
            <pre>{
    "order_id": 1,
    "payment_method": "card"
}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/payments/{id}/confirm</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Confirmer un paiement</p>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/payments/{id}/status</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Vérifier le statut d'un paiement</p>
        </div>

        <h2>🔔 Notifications</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/notifications</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Lister les notifications de l'utilisateur</p>
        </div>

        <div class="endpoint">
            <span class="method put">PUT</span> <span class="path">/notifications/{id}/read</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Marquer une notification comme lue</p>
        </div>

        <hr style="border-color: #30363d; margin: 40px 0;">

        <h2>🔑 Authentification</h2>
        <p style="color: #8b949e;">Pour accéder aux routes protégées, inclure le token dans le header :</p>
        <pre>Authorization: Bearer &lt;votre_token_jwt&gt;</pre>

        <h2>📌 Codes de statut</h2>
        <ul style="color: #8b949e; list-style: none; padding: 0;">
            <li>✅ <strong>200</strong> - Succès</li>
            <li>✅ <strong>201</strong> - Créé avec succès</li>
            <li>⚠️ <strong>400</strong> - Erreur de validation</li>
            <li>🔒 <strong>401</strong> - Non authentifié</li>
            <li>🚫 <strong>403</strong> - Accès interdit</li>
            <li>❌ <strong>404</strong> - Ressource non trouvée</li>
            <li>💥 <strong>500</strong> - Erreur serveur</li>
        </ul>
    </div>
</body>
</html>
EOFcat > /workspaces/ikaboutique-backend/public/docs.html << 'EOF'

<html>
<head>
    <meta charset="UTF-8">
    <title>IKABOUTIQUE API - Documentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #0d1117; color: #e6edf3; padding: 40px; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { font-size: 32px; color: #f0f6fc; border-bottom: 2px solid #30363d; padding-bottom: 15px; }
        h2 { font-size: 24px; color: #f0f6fc; margin-top: 40px; margin-bottom: 20px; }
        .info { background: #161b22; padding: 15px 20px; border-radius: 8px; border-left: 4px solid #58a6ff; margin-bottom: 30px; }
        .info code { background: #21262d; padding: 2px 8px; border-radius: 4px; }
        .endpoint { background: #161b22; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #30363d; }
        .endpoint:hover { border-color: #58a6ff; }
        .method { display: inline-block; padding: 4px 12px; border-radius: 6px; font-weight: bold; color: white; font-size: 13px; }
        .get { background: #1f7b4d; }
        .post { background: #1a6e8a; }
        .put { background: #9e6a03; }
        .delete { background: #b62324; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 10px; }
        .badge-public { background: #1f7b4d; color: #fff; }
        .badge-private { background: #b62324; color: #fff; }
        .path { font-family: 'Courier New', monospace; color: #f0f6fc; font-size: 14px; }
        pre { background: #0d1117; padding: 15px; border-radius: 8px; overflow-x: auto; margin-top: 10px; border: 1px solid #30363d; font-size: 13px; }
        code { font-family: 'Courier New', monospace; }
        .example { margin-top: 10px; }
        .example-text { color: #8b949e; font-size: 13px; }
        .badge-auth { background: #da3633; color: white; padding: 2px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 IKABOUTIQUE API V2</h1>
        <p style="color: #8b949e; margin-bottom: 20px;">Documentation complète de l'API e-commerce IKABOUTIQUE</p>

        <div class="info">
            <strong>🌐 Base URL :</strong> <code>http://127.0.0.1:8000/api/v1</code><br>
            <strong>🔑 Test Account :</strong> <code>vendeur@test.com</code> / <code>password123</code><br>
            <strong>📱 Test Phone :</strong> <code>+22378088551</code>
        </div>

        <h2>🔐 Authentification</h2>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/register</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Créer un nouveau compte utilisateur</p>
            <pre>{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+22378088551",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "customer"
}</pre>
            <div class="example-text">📌 <strong>Rôle :</strong> customer ou seller</div>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/login</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Se connecter et obtenir un token JWT</p>
            <pre>{
    "email": "vendeur@test.com",
    "password": "password123"
}</pre>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/auth/me</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Obtenir les informations de l'utilisateur connecté</p>
            <pre>Authorization: Bearer {{token}}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/logout</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Déconnecter l'utilisateur et invalider le token</p>
            <pre>Authorization: Bearer {{token}}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/auth/refresh</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Rafraîchir le token JWT</p>
            <pre>Authorization: Bearer {{token}}</pre>
        </div>

        <h2>📦 Produits</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/products</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Lister tous les produits</p>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/products/{id}</span>
            <span class="badge badge-public">Public</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Voir les détails d'un produit</p>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/products</span>
            <span class="badge badge-private">Protégé</span>
            <span class="badge-auth">Seller/Admin</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Créer un nouveau produit</p>
            <pre>{
    "name": "Mon produit",
    "slug": "mon-produit",
    "description": "Description",
    "price": 99.99,
    "stock": 10,
    "category_id": 1,
    "shop_id": 1
}</pre>
        </div>

        <div class="endpoint">
            <span class="method put">PUT</span> <span class="path">/products/{id}</span>
            <span class="badge badge-private">Protégé</span>
            <span class="badge-auth">Seller/Admin</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Modifier un produit existant</p>
        </div>

        <div class="endpoint">
            <span class="method delete">DELETE</span> <span class="path">/products/{id}</span>
            <span class="badge badge-private">Protégé</span>
            <span class="badge-auth">Seller/Admin</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Supprimer un produit</p>
        </div>

        <h2>🛒 Panier</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/cart</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Voir le contenu du panier</p>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/cart/add</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Ajouter un produit au panier</p>
            <pre>{
    "product_id": 1,
    "quantity": 2
}</pre>
        </div>

        <div class="endpoint">
            <span class="method delete">DELETE</span> <span class="path">/cart/clear</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Vider le panier</p>
        </div>

        <h2>📦 Commandes</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/orders</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Lister les commandes de l'utilisateur</p>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/orders</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Créer une nouvelle commande</p>
            <pre>{
    "shipping_address": "123 Rue Test",
    "billing_address": "123 Rue Test",
    "payment_method": "card"
}</pre>
        </div>

        <h2>💳 Paiements</h2>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/payments/initiate</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Initier un paiement</p>
            <pre>{
    "order_id": 1,
    "payment_method": "card"
}</pre>
        </div>

        <div class="endpoint">
            <span class="method post">POST</span> <span class="path">/payments/{id}/confirm</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Confirmer un paiement</p>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/payments/{id}/status</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Vérifier le statut d'un paiement</p>
        </div>

        <h2>🔔 Notifications</h2>

        <div class="endpoint">
            <span class="method get">GET</span> <span class="path">/notifications</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Lister les notifications de l'utilisateur</p>
        </div>

        <div class="endpoint">
            <span class="method put">PUT</span> <span class="path">/notifications/{id}/read</span>
            <span class="badge badge-private">Protégé</span>
            <p style="margin-top: 10px; color: #8b949e; font-size: 14px;">Marquer une notification comme lue</p>
        </div>

        <hr style="border-color: #30363d; margin: 40px 0;">

        <h2>🔑 Authentification</h2>
        <p style="color: #8b949e;">Pour accéder aux routes protégées, inclure le token dans le header :</p>
        <pre>Authorization: Bearer &lt;votre_token_jwt&gt;</pre>

        <h2>📌 Codes de statut</h2>
        <ul style="color: #8b949e; list-style: none; padding: 0;">
            <li>✅ <strong>200</strong> - Succès</li>
            <li>✅ <strong>201</strong> - Créé avec succès</li>
            <li>⚠️ <strong>400</strong> - Erreur de validation</li>
            <li>🔒 <strong>401</strong> - Non authentifié</li>
            <li>🚫 <strong>403</strong> - Accès interdit</li>
            <li>❌ <strong>404</strong> - Ressource non trouvée</li>
            <li>💥 <strong>500</strong> - Erreur serveur</li>
        </ul>
    </div>
</body>
</html>
