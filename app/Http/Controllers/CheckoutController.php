<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\RajaOngkirService;
use App\Services\MidtransHttp;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(private RajaOngkirService $ongkir) {}

    public function show(Request $request)
    {
        $cart = Cart::with('items.product')->firstOrCreate(['user_id' => $request->user()->id]);
        abort_if($cart->items->isEmpty(), 302, '', ['Location' => route('cart.index')]);
        $couriers = config('services.rajaongkir.couriers');
        return view('checkout.index', compact('cart', 'couriers'));
    }

    public function searchDestination(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);
        return response()->json($this->ongkir->searchDestination($request->q, 12));
    }

    public function costs(Request $request)
    {
        $request->validate([
            'destination_id' => 'required|integer',
            'courier'        => 'required|string',
            'weight'         => 'required|integer|min:1',
        ]);
        $origin = (int) config('services.rajaongkir.origin');
        $costs  = $this->ongkir->cost($origin, (int) $request->destination_id, (int) $request->weight, $request->courier, 'lowest');
        return response()->json($costs);
    }

    public function store(Request $request)
    {
        $courierList = implode(',', config('services.rajaongkir.couriers'));
        $request->validate([
            'recipient_name'  => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:20',
            'destination_id'  => 'required|integer',
            'address'         => 'required|string|max:255',
            'postal_code'     => 'nullable|string|max:10',
            'courier'         => "required|string|in:$courierList",
            'service'         => 'required|string|max:50',
            'shipping_cost'   => 'required|integer|min:0',
        ]);

        $cart = Cart::with('items.product')->firstOrCreate(['user_id' => $request->user()->id]);
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Keranjang kosong.');
        }

        $weight = $cart->totalWeight();
        $serverCosts = collect(app(RajaOngkirService::class)->cost(
            (int) config('services.rajaongkir.origin'),
            (int) $request->destination_id,
            $weight,
            $request->courier,
            'lowest'
        ));
        $found = $serverCosts->first(fn($c) => ($c['service'] ?? '') === $request->service);
        abort_unless($found && ((int) ($found['value'] ?? -1) === (int) $request->shipping_cost), 422, 'Tarif ongkir tidak valid.');

        $subtotal = $cart->subtotal();
        $shipping = (int) $request->shipping_cost;
        $total    = $subtotal + $shipping;

        $order = DB::transaction(function () use ($request, $cart, $subtotal, $shipping, $total) {
            $order = Order::create([
                'user_id'         => $cart->user_id,
                'recipient_name'  => $request->recipient_name,
                'recipient_phone' => $request->recipient_phone,
                'province_id'     => 0,
                'city_id'         => 0,
                'address'         => $request->address,
                'postal_code'     => $request->postal_code,
                'courier'         => $request->courier,
                'service'         => $request->service,
                'shipping_cost'   => $shipping,
                'subtotal'        => $subtotal,
                'total'           => $total,
                'status'          => 'draft',
                'payment_gateway' => 'midtrans',
            ]);

            foreach ($cart->items as $ci) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $ci->product_id,
                    'qty'        => $ci->qty,
                    'price'      => $ci->price,
                    'line_total' => $ci->price * $ci->qty,
                ]);
            }

            $cart->items()->delete();
            return $order;
        });

        $midOrderId = 'ORDER-' . $order->id . '-' . Str::upper(Str::random(5));

        $items = $order->items()->with('product')->get()->map(function ($i) {
            return [
                'id'       => (string) $i->product_id,
                'price'    => (int) $i->price,
                'quantity' => (int) $i->qty,
                'name'     => substr($i->product->name, 0, 50),
            ];
        })->values()->all();

        $items[] = [
            'id'       => 'SHIPPING',
            'price'    => (int) $order->shipping_cost,
            'quantity' => 1,
            'name'     => 'Ongkos Kirim',
        ];

        $payload = [
            'transaction_details' => [
                'order_id'     => $midOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'email'      => $request->user()->email,
                'phone'      => $order->recipient_phone,
                'shipping_address' => [
                    'first_name'  => $order->recipient_name,
                    'phone'       => $order->recipient_phone,
                    'address'     => $order->address,
                    'postal_code' => $order->postal_code,
                    'country_code'=> 'IDN',
                ],
            ],
            'item_details' => $items,
            'callbacks' => [
                'finish' => route('orders.finish', $order),
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit'       => 'hours',
                'duration'   => 24,
            ],
        ];

        try {
            $snap = app(MidtransHttp::class)->createSnap($payload);
        } catch (\Throwable $e) {
            Log::error('Create Snap exception', ['msg' => $e->getMessage()]);
            return redirect()->route('products.index')->with('status', 'Gagal membuat pembayaran. Coba lagi nanti.');
        }

        $token = $snap['token'] ?? null;
        $redir = $snap['redirect_url'] ?? null;
        if (!$token || !$redir) {
            Log::error('Create Snap missing token/redirect', ['snap_response' => $snap]);
            return redirect()->route('products.index')->with('status', 'Gagal memproses pembayaran. Silakan coba lagi.');
        }

        $order->update([
            'midtrans_order_id'    => $midOrderId,
            'status'               => 'pending_payment',
            'payment_token'        => $token,
            'payment_redirect_url' => $redir,
            'midtrans_payload'     => $snap['raw'] ?? null,
        ]);

        return redirect()->away($redir);
    }
}
