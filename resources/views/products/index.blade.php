<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Katalog Produk
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @forelse ($products as $p)
                    <a href="{{ route('products.show', $p->slug) }}"
                       class="block rounded-xl overflow-hidden border hover:shadow transition">
                        <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="h-40 object-cover">
                        <div class="p-4">
                            <h3 class="font-semibold text-lg">{{ $p->name }}</h3>
                            <p class="text-gray-600 text-sm line-clamp-2">{{ Str::limit($p->description, 80) }}</p>
                            <div class="mt-2 font-bold">{{ $p->price_formatted }}</div>
                        </div>
                    </a>
                @empty
                    <p>Tidak ada produk.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
