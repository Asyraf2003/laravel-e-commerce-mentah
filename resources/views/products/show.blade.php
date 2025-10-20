<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="text-sm text-blue-600 underline">← Kembali</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $product->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-40 object-cover">
                <div class="p-6 space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
                            <div class="text-gray-500">Berat: {{ $product->weight }} gram</div>
                        </div>
                        <div class="text-2xl font-extrabold">{{ $product->price_formatted }}</div>
                    </div>

                    <p class="leading-relaxed text-gray-700 dark:text-gray-200">
                        {{ $product->description }}
                    </p>

                    <div class="flex items-center gap-3">
                        @auth
                            <form method="POST" action="{{ route('cart.add', $product->slug) }}">
                                @csrf
                                <input type="hidden" name="qty" value="1">
                                <button class="px-4 py-2 rounded-lg border hover:bg-gray-50">
                                    Tambah ke Keranjang
                                </button>
                            </form>

                            <a href="{{ route('checkout.show') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                Checkout
                            </a>
                        @endauth

                        @guest
                            <a href="{{ route('login') }}"
                               class="px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600">
                                Login untuk membeli
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
