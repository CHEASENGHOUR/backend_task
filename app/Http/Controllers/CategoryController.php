<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Order;
class CategoryController extends Controller
{
    //
    public function index()
    {
        // Load categories with products
        $categories = Category::with(['products:id,category_id,name,image'])
            ->orderBy('sort_order')
            ->get();

        $result = $categories->map(function ($category) {
            $latestOrderNo = Order::whereHas('orderItems.product', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })->latest()->value('order_no');

            return [
                'id' => $category->id,
                'name' => $category->name,
                'latest_order_no' => $latestOrderNo,
                // Map all products for this category
                'products' => $category->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image ? asset('storage/' . $product->image) : null,
                    ];
                }),
            ];
        });


        return response()->json(['success' => true, 'categories' => $result]);
    }
}
