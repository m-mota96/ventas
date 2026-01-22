<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'bar_code', 'content', 'abreviation', 'type_sale', 'description', 'created_by', 'updated_by', 'deleted_by', 
    ];

    public function productStore() {
        return $this->hasOne(ProductStore::class);
    }

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }
}
