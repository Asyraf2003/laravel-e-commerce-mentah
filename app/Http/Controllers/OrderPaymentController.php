<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransHttp;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    public function finish(Request $request, Order $order, MidtransHttp $mid)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $midId = $order->midtrans_order_id ?: ('ORDER-' . $order->id);
        $st = $mid->status($midId);

        $tx = $st['transaction_status'] ?? null;
        $fraud = $st['fraud_status'] ?? null;

        $new = $order->status;
        if ($tx === 'capture') {
            $new = ($fraud ?? '') === 'accept' ? 'paid' : 'pending_review';
        } elseif ($tx === 'settlement') {
            $new = 'paid';
        } elseif (in_array($tx, ['cancel','deny','expire'])) {
            $new = 'cancelled';
        } else {
            $new = 'pending_payment';
        }

        $order->update([
            'status'           => $new,
            'midtrans_status'  => $tx . ($fraud ? " ($fraud)" : ''),
            'midtrans_payload' => $st,
        ]);

        return redirect()->route('products.index')->with('status', "Status pembayaran: $new");
    }

    public function refresh(Request $request, Order $order, MidtransHttp $mid)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $midId = $order->midtrans_order_id ?: ('ORDER-' . $order->id);
        $st = $mid->status($midId);

        $tx = $st['transaction_status'] ?? null;
        $fraud = $st['fraud_status'] ?? null;

        $new = $order->status;
        if ($tx === 'capture' || $tx === 'settlement') $new = 'paid';
        elseif (in_array($tx, ['cancel','deny','expire'])) $new = 'cancelled';
        elseif ($tx === 'pending') $new = 'pending_payment';

        $order->update([
            'status'           => $new,
            'midtrans_status'  => $tx . ($fraud ? " ($fraud)" : ''),
            'midtrans_payload' => $st,
        ]);

        return back()->with('status', "Status pembayaran diperbarui: $new");
    }
}
