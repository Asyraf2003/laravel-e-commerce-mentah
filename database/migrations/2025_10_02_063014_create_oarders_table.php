<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // alamat & penerima
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->unsignedInteger('province_id');
            $table->unsignedInteger('city_id');
            $table->string('address');
            $table->string('postal_code', 10)->nullable();

            // ongkir
            $table->string('courier', 10);
            $table->string('service', 50);
            $table->unsignedInteger('shipping_cost');

            // ringkasan biaya
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('total');

            // pembayaran
            $table->string('payment_gateway')->nullable();
            $table->string('payment_token')->nullable();
            $table->string('payment_redirect_url')->nullable();
            $table->string('midtrans_status')->nullable();
            $table->json('midtrans_payload')->nullable();
            $table->string('midtrans_order_id')->nullable();

            // status awal: draft → pending_payment (nanti Midtrans) → paid/cancelled
            $table->string('status', 30)->default('draft');

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
