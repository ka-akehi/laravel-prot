<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawOrder extends Model
{
    protected $fillable = ['customer_name', 'phone_number', 'products', 'prices', 'address'];
}
