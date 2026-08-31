<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Produits</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #2c3e50; color: white; padding: 10px; text-align: left; }
        td { padding: 8px 10px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>📦 Liste des produits</h1>
    <p>Date: {{ date('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Catégorie</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->price, 2) }} FCFA</td>
                <td>{{ $product->stock }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td>{{ $product->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total produits:</strong> {{ count($products) }}</p>
</body>
</html>
