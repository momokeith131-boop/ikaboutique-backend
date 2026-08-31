<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController
{
    // Générer une facture PDF
    public function generate($orderId)
    {
        $order = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Ajouter les informations du client
        $order->customer_name = Auth::user()->name;
        $order->customer_email = Auth::user()->email;
        $order->customer_phone = Auth::user()->phone;

        $pdf = Pdf::loadView('invoices.order', ['order' => $order]);

        return $pdf->download('facture_' . $order->id . '.pdf');
    }

    // Voir une facture en ligne
    public function view($orderId)
    {
        $order = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Commande non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $order->customer_name = Auth::user()->name;
        $order->customer_email = Auth::user()->email;
        $order->customer_phone = Auth::user()->phone;

        $pdf = Pdf::loadView('invoices.order', ['order' => $order]);

        return $pdf->stream('facture_' . $order->id . '.pdf');
    }
}
