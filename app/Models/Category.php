<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $fillable = ['name', 'sort_order'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function latestOrder()
    {
        return $this->hasManyThrough(
            Order::class,
            OrderItem::class,
            'product_id', // Foreign key on order_items
            'id',         // Foreign key on orders
            'id',         // Local key on categories
            'order_id'    // Local key on order_items
        )->latest('created_at');
    }
}
