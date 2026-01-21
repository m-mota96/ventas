<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id', 'status_id', 'payment_method_id', 'cash', 'card', 'total', 'created_by', 'updated_by', 'deleted_by',
    ];

    public function inventory() {
        return $this->hasMany(Inventory::class);
    }

    public function store() {
        return $this->belongsTo(Store::class);
    }

    public function status() {
        return $this->belongsTo(SaleStatus::class, 'status_id', 'id');
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }
    
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
    
    public function deletedBy() {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
