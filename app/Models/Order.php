<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id','recipient_name','recipient_phone','province_id','city_id','address','postal_code',
        'courier','service','shipping_cost','subtotal','total','status',
        'midtrans_order_id','payment_gateway','payment_token','payment_redirect_url','midtrans_status','midtrans_payload',
    ];

    protected $casts = [
        'midtrans_payload' => 'array',
    ];

    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
}
