<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['address'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
