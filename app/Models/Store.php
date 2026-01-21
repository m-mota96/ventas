<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'address', 'status', 'created_by', 'updated_by', 'deleted_by'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function users() {
        return $this->HasMany(User::class);
    }

    public function storeProduct() {
        return $this->hasOne(ProductStore::class);
    }

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }
}
