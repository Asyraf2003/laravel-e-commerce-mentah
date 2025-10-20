<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Keranjang</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-6 px-4">
        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
        @endif

        @if ($cart->items->isEmpty())
            <p>Keranjang kosong. <a class="text-blue-600 underline" href="{{ route('products.index') }}">Belanja dulu</a>.</p>
        @else
            <div class="space-y-4">
                @foreach ($cart->items as $item)
                    <div class="flex items-center justify-between border rounded p-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item->product->image_url }}" class="w-20 h-20 object-cover rounded" alt="">
                            <div>
                                <div class="font-semibold">{{ $item->product->name }}</div>
                                <div class="text-sm text-gray-600">Harga: Rp {{ number_format($item->price,0,',','.') }}</div>
                                <div class="text-sm text-gray-600">Berat: {{ $item->product->weight }} g</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="number" name="qty" value="{{ $item->qty }}" min="1" class="w-20 border rounded px-2 py-1">
                                <button class="px-3 py-1 border rounded">Update</button>
                            </form>

                            <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1 border rounded text-red-600">Hapus</button>
                            </form>
                        </div>

                        <div class="font-bold">
                            Rp {{ number_format($item->lineTotal(),0,',','.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-between">
                <div class="text-lg">Subtotal:
                    <span class="font-bold">Rp {{ number_format($cart->subtotal(),0,',','.') }}</span>
                </div>
                <a href="{{ route('checkout.show') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Checkout</a>
            </div>
        @endif
    </div>
</x-app-layout>
