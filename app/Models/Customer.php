<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone_number'];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
