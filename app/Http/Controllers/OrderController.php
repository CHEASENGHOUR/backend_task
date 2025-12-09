<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
class OrderController extends Controller
{
    //
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $order = Order::create([
            'order_no' => 'ORD-' . now()->format('YmdHis'),
            'customer_name' => $data['customer_name'],
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::find($item['product_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->total_price,
                'total' => $product->total_price * $item['quantity'],
            ]);
        }

        return response()->json(['success' => true, 'order_no' => $order->order_no]);
    }
}
