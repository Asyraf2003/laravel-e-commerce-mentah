<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(CartItem::class); }

    public function subtotal(): int {
        return (int) $this->items->sum(fn($i) => $i->price * $i->qty);
    }

    public function totalWeight(): int {
        return (int) $this->items->sum(fn($i) => ($i->product->weight ?? 0) * $i->qty);
    }

    public function addProduct(Product $product, int $qty = 1): void {
        $item = $this->items()->firstOrCreate(
            ['product_id' => $product->id],
            ['price' => $product->price, 'qty' => 0]
        );
        $item->qty += max(1, $qty);
        $item->price = $product->price; // refresh snapshot
        $item->save();
    }
}
