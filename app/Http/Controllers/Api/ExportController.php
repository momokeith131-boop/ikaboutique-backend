<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController
{
    // ========== CSV EXPORTS ==========

    // Exporter les commandes en CSV
    public function ordersCsv(Request $request)
    {
        $query = Order::where('shop_id', $request->shop_id ?? Auth::user()->shop->id ?? 0);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->with('items.product')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="commandes_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Client', 'Total', 'Statut', 'Date']);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->customer_name ?? 'N/A',
                    $order->total,
                    $order->status,
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Exporter les produits en CSV
    public function productsCsv(Request $request)
    {
        $query = Product::where('shop_id', $request->shop_id ?? Auth::user()->shop->id ?? 0);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $products = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="produits_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Prix', 'Stock', 'Catégorie', 'Date']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->price,
                    $product->stock,
                    $product->category->name ?? 'N/A',
                    $product->created_at->format('d/m/Y'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Exporter les clients en CSV
    public function customersCsv(Request $request)
    {
        $query = User::where('role', 'customer');

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%');
        }

        $customers = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clients_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Email', 'Téléphone', 'Date']);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->phone,
                    $customer->created_at->format('d/m/Y'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ========== PDF EXPORTS ==========

    // Exporter les commandes en PDF
    public function ordersPdf(Request $request)
    {
        $query = Order::where('shop_id', $request->shop_id ?? Auth::user()->shop->id ?? 0);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->with('items.product')->get();

        $pdf = Pdf::loadView('exports.orders', ['orders' => $orders]);
        return $pdf->download('commandes_' . date('Y-m-d') . '.pdf');
    }

    // Exporter les produits en PDF
    public function productsPdf(Request $request)
    {
        $products = Product::where('shop_id', $request->shop_id ?? Auth::user()->shop->id ?? 0)->get();

        $pdf = Pdf::loadView('exports.products', ['products' => $products]);
        return $pdf->download('produits_' . date('Y-m-d') . '.pdf');
    }

    // Exporter les clients en PDF
    public function customersPdf(Request $request)
    {
        $customers = User::where('role', 'customer')->get();

        $pdf = Pdf::loadView('exports.customers', ['customers' => $customers]);
        return $pdf->download('clients_' . date('Y-m-d') . '.pdf');
    }
}
