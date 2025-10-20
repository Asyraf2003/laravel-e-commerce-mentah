<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        $orderId = $data['order_id'] ?? null;
        $status  = $data['transaction_status'] ?? null;
        $fraud   = $data['fraud_status'] ?? null;
        $gross   = $data['gross_amount'] ?? null;
        $sig     = $data['signature_key'] ?? null;

        if (!$orderId || !$sig) return response()->json(['message'=>'Bad payload'], 400);

        $serverKey  = (string) config('services.midtrans.server_key');
        $statusCode = $data['status_code'] ?? '200';
        $check = hash('sha512', $orderId . $statusCode . $gross . $serverKey);
        if (!hash_equals($check, $sig)) return response()->json(['message'=>'Invalid signature'], 403);

        $localId = null;
        if (preg_match('/^ORDER-(\d+)/', (string) $orderId, $m)) {
            $localId = (int) $m[1];
        }
        if (!$localId) return response()->json(['message'=>'Invalid order id format'], 422);

        $order = Order::find($localId);
        if (!$order) return response()->json(['message'=>'Order not found'], 404);

        $new = $order->status;
        if ($status === 'capture' || $status === 'settlement') $new = 'paid';
        elseif (in_array($status, ['cancel','deny','expire'])) $new = 'cancelled';
        elseif ($status === 'pending') $new = 'pending_payment';

        $order->update([
            'status' => $new,
            'midtrans_status' => $status . ($fraud ? " ($fraud)" : ''),
            'midtrans_payload' => $data,
        ]);

        return response()->json(['message'=>'OK']);
    }
}
