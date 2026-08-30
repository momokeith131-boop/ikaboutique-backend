#!/bin/bash

# Couleurs
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}     IKABOUTIQUE - TEST API${NC}"
echo -e "${BLUE}========================================${NC}"

# Connexion
echo -e "\n${YELLOW}🔐 Connexion...${NC}"
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"vendeur@test.com","password":"password123"}' \
  | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ Erreur de connexion${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Connecté !${NC}"
echo -e "Token: ${TOKEN:0:50}...\n"

# 1. Lister les produits
echo -e "${YELLOW}📦 1. Liste des produits${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/products?per_page=5" \
  -H "Authorization: Bearer $TOKEN" | jq '.data[] | {id, name, price}' 2>/dev/null || echo "Aucun produit"

# 2. Recherche
echo -e "\n${YELLOW}🔍 2. Recherche 'mon'${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/products?search=mon&per_page=5" \
  -H "Authorization: Bearer $TOKEN" | jq '.data[] | {id, name, price}' 2>/dev/null || echo "Aucun résultat"

# 3. Filtre prix
echo -e "\n${YELLOW}💰 3. Filtre prix (50-150)${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/products?price_min=50&price_max=150&per_page=5" \
  -H "Authorization: Bearer $TOKEN" | jq '.data[] | {id, name, price}' 2>/dev/null || echo "Aucun résultat"

# 4. Panier - Voir
echo -e "\n${YELLOW}🛒 4. Voir le panier${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/cart" \
  -H "Authorization: Bearer $TOKEN" | jq '.' 2>/dev/null || echo "Panier vide"

# 5. Panier - Ajouter
echo -e "\n${YELLOW}➕ 5. Ajouter au panier (produit 1, qty 2)${NC}"
curl -s -X POST "http://127.0.0.1:8000/api/v1/cart/add" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":2}' | jq '.message // .' 2>/dev/null

# 6. Panier - Voir après ajout
echo -e "\n${YELLOW}🛒 6. Voir le panier après ajout${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/cart" \
  -H "Authorization: Bearer $TOKEN" | jq '.' 2>/dev/null

# 7. Créer une commande
echo -e "\n${YELLOW}📦 7. Créer une commande${NC}"
curl -s -X POST "http://127.0.0.1:8000/api/v1/orders" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"shipping_address":"123 Rue Test","billing_address":"123 Rue Test","payment_method":"card"}' \
  | jq '.message // .' 2>/dev/null

# 8. Lister les commandes
echo -e "\n${YELLOW}📋 8. Lister les commandes${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/orders" \
  -H "Authorization: Bearer $TOKEN" | jq '.[] | {id, total, status}' 2>/dev/null || echo "Aucune commande"

# 9. Notifications
echo -e "\n${YELLOW}🔔 9. Lister les notifications${NC}"
curl -s -X GET "http://127.0.0.1:8000/api/v1/notifications" \
  -H "Authorization: Bearer $TOKEN" | jq '.notifications[] | {id, title, is_read}' 2>/dev/null || echo "Aucune notification"

echo -e "\n${GREEN}✅ Tests terminés !${NC}"
