<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Checkout</h2></x-slot>

    <div class="max-w-5xl mx-auto py-6 px-4">
        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-4">
                <div class="border rounded p-4">
                    <h3 class="font-semibold mb-3">Alamat Pengiriman</h3>
                    <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="space-y-3">
                        @csrf

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm">Nama Penerima</label>
                                <input name="recipient_name" required class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">No. HP</label>
                                <input name="recipient_phone" required class="w-full border rounded px-3 py-2">
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <h3 class="font-semibold mb-2">Tujuan Pengiriman</h3>
                            <div class="space-y-2">
                                <label class="text-sm">Cari kota/kecamatan/kelurahan atau kode pos</label>
                                <input id="dest-search" type="text" class="w-full border rounded px-3 py-2" placeholder="cth: jakarta, denpasar, mataram 83112">
                                <div id="dest-results" class="border rounded divide-y max-h-52 overflow-auto hidden"></div>
                                <input type="hidden" name="destination_id" id="destination_id" required>
                                <small id="dest-picked" class="text-gray-600"></small>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm">Alamat Lengkap</label>
                            <textarea name="address" required class="w-full border rounded px-3 py-2" rows="3"></textarea>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm">Kode Pos (opsional)</label>
                                <input name="postal_code" class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm">Kurir</label>
                                <select id="courier" name="courier" required class="w-full border rounded px-3 py-2">
                                    <option value="">-- Pilih Kurir --</option>
                                    @foreach ($couriers as $c)
                                        <option value="{{ $c }}">{{ strtoupper($c) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="border rounded p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold">Layanan & Ongkir</div>
                                    <div class="text-sm text-gray-600">Klik "Hitung Ongkir" lalu pilih layanan.</div>
                                </div>
                                <button type="button" id="btn-cost" class="px-3 py-2 border rounded">Hitung Ongkir</button>
                            </div>
                            <div id="services" class="mt-3 space-y-2"></div>
                        </div>

                        <input type="hidden" name="service" id="service">
                        <input type="hidden" name="shipping_cost" id="shipping_cost" value="0">

                        <div class="flex items-center justify-between mt-4">
                            <div>
                                Subtotal: <b>Rp {{ number_format($cart->subtotal(),0,',','.') }}</b><br>
                                Berat total: <b>{{ $cart->totalWeight() }} g</b><br>
                                Ongkir: <b id="ongkir-text">Rp 0</b><br>
                                <span class="text-lg">Total: <b id="total-text">Rp {{ number_format($cart->subtotal(),0,',','.') }}</b></span>
                            </div>

                            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Buat Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="border rounded p-4">
                <h3 class="font-semibold mb-3">Ringkasan Keranjang</h3>
                <div class="space-y-2">
                    @foreach ($cart->items as $i)
                        <div class="flex justify-between">
                            <div>{{ $i->product->name }} × {{ $i->qty }}</div>
                            <div>Rp {{ number_format($i->lineTotal(),0,',','.') }}</div>
                        </div>
                    @endforeach
                    <hr>
                    <div class="flex justify-between font-semibold">
                        <div>Subtotal</div>
                        <div>Rp {{ number_format($cart->subtotal(),0,',','.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const rupiah = n => new Intl.NumberFormat('id-ID').format(n);

    const courier  = document.getElementById('courier');
    const btnCost  = document.getElementById('btn-cost');
    const services = document.getElementById('services');

    const serviceInput  = document.getElementById('service');
    const shippingInput = document.getElementById('shipping_cost');
    const ongkirText    = document.getElementById('ongkir-text');
    const totalText     = document.getElementById('total-text');

    const subtotal = {{ $cart->subtotal() }};
    const weight   = {{ $cart->totalWeight() }};

    const destSearch  = document.getElementById('dest-search');
    const destResults = document.getElementById('dest-results');
    const destPicked  = document.getElementById('dest-picked');
    const destInput   = document.getElementById('destination_id');

    async function searchDest(q) {
        const url = `{{ route('checkout.search-destination') }}` + `?q=${encodeURIComponent(q)}`;
        const res = await fetch(url);
        return await res.json();
    }

    let timer;
    destSearch.addEventListener('input', e => {
        clearTimeout(timer);
        const q = e.target.value.trim();
        if (q.length < 2) { destResults.classList.add('hidden'); return; }
        timer = setTimeout(async () => {
            destResults.innerHTML = 'Mencari...';
            destResults.classList.remove('hidden');
            const data = await searchDest(q);
            if (!Array.isArray(data) || data.length === 0) {
                destResults.innerHTML = '<div class="p-2 text-gray-500">Tidak ditemukan.</div>';
                return;
            }
            destResults.innerHTML = '';
            data.forEach(row => {
                const div = document.createElement('div');
                div.className = 'p-2 hover:bg-gray-50 cursor-pointer';
                const label = row.label ?? `${row.subdistrict_name ?? ''} ${row.district_name ?? ''} ${row.city_name ?? ''} ${row.province_name ?? ''}`.trim();
                div.textContent = label;
                div.addEventListener('click', () => {
                    destInput.value = row.id;
                    destPicked.textContent = `Tujuan: ${label}`;
                    destResults.classList.add('hidden');
                });
                destResults.appendChild(div);
            });
        }, 300);
    });

    btnCost.addEventListener('click', async () => {
        if (!destInput.value || !courier.value) { alert('Pilih tujuan dan kurir dulu.'); return; }
        services.innerHTML = 'Menghitung ongkir...';
        const res = await fetch(`{{ route('checkout.costs') }}`, {
            method:'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({
                destination_id: +destInput.value,
                courier: courier.value,
                weight: weight || 1
            })
        });
        const data = await res.json();

        if (!Array.isArray(data) || data.length === 0) { services.innerHTML = 'Tidak ada layanan.'; return; }

        services.innerHTML = '';
        data.forEach((opt, idx) => {
            const id = `svc_${idx}`;
            services.innerHTML += `
                <label class="flex items-center justify-between border rounded p-2 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <input type="radio" name="svc" id="${id}" value="${opt.service}" data-cost="${opt.value}">
                        <div>
                            <div class="font-semibold">${(opt.courier || '').toUpperCase()} - ${opt.service}</div>
                            <div class="text-sm text-gray-600">${opt.description ?? ''} ${opt.etd ? `(ETD ${opt.etd} hari)` : ''}</div>
                        </div>
                    </div>
                    <div class="font-bold">Rp ${rupiah(opt.value)}</div>
                </label>`;
        });

        document.querySelectorAll('input[name="svc"]').forEach(r => {
            r.addEventListener('change', e => {
                const cost = +e.target.dataset.cost;
                serviceInput.value = e.target.value;
                shippingInput.value = cost;
                updateTotals();
            });
        });
    });

    function updateTotals() {
        const ship = +shippingInput.value || 0;
        ongkirText.textContent = 'Rp ' + rupiah(ship);
        totalText.textContent  = 'Rp ' + rupiah(subtotal + ship);
    }
    </script>
</x-app-layout>
