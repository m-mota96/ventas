<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductStore extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'store_id',
        'price',
        'discount',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }
}
