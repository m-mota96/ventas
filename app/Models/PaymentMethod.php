<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'payment_method', 'default', 'status'
    ];

    public $timestamps = false;
}
