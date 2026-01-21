<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Inventory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'product_id',
        'store_id',
        'reference_id',
        'sale_id',
        'type',
        'quantity',
        'price',
        'discount',
        'batch',
        'expiration_date',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function reference() {
        return $this->belongsTo(Reference::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
