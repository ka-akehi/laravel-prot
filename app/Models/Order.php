<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['customer_name', 'phone_number', 'address'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
