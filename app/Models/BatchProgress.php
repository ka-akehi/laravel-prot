<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchProgress extends Model
{
    protected $fillable = [
        'batch_name',
        'total',
        'processed',
        'status',
    ];
}
