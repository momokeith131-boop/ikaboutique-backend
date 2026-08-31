<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
        }
        .header p {
            color: #7f8c8d;
            margin: 5px 0;
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            width: 45%;
        }
        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .info-box p {
            margin: 3px 0;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        .total {
            text-align: right;
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #7f8c8d;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>IKABOUTIQUE</h1>
        <p>Facture #{{ $order->id }}</p>
        <p>Date: {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <div class="info-box">
            <strong>🧑‍💼 Client</strong>
            <p>{{ $order->customer_name ?? 'Client' }}</p>
            <p>Email: {{ $order->customer_email ?? 'N/A' }}</p>
            <p>Téléphone: {{ $order->customer_phone ?? 'N/A' }}</p>
        </div>
        <div class="info-box">
            <strong>📦 Livraison</strong>
            <p>{{ $order->shipping_address ?? 'N/A' }}</p>
            <p>Statut: {{ $order->status }}</p>
            <p>Méthode: {{ $order->payment_method ?? 'N/A' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'Produit' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }} FCFA</td>
                <td>{{ number_format($item->quantity * $item->price, 2) }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        Total: {{ number_format($order->total, 2) }} FCFA
    </div>

    <div class="footer">
        <p>Merci pour votre commande !</p>
        <p>IKABOUTIQUE - Votre boutique en ligne</p>
    </div>
</body>
</html>
