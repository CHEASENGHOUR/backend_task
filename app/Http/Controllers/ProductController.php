<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Exception;

class ProductController extends Controller
{

    public function index()
    {
        // Fetch all products with their category
        $products = Product::with('category')->get();

        // Optional: map category name for easier use in frontend
        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'price' => $product->price,
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'total_price' => $product->total_price,
                'description' => $product->description,
                'category_name' => $product->category ? $product->category->name : null,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
            ];
        });

        return response()->json($products);
    }
    public function store(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'name' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image', // make optional for testing
                'price' => 'required|numeric',
                'discount_type' => 'nullable|in:percent,amount',
                'discount_value' => 'nullable|numeric',
                'description' => 'nullable|string',
            ]);

            // Check category exists
            $category = Category::find($request->category_id);
            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Category not found'], 404);
            }

            // Calculate total price
            $total_price = $request->price;
            if ($request->discount_type == 'percent') {
                $total_price -= ($request->price * $request->discount_value / 100);
            } elseif ($request->discount_type == 'amount') {
                $total_price -= $request->discount_value;
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // Create product
            $product = Product::create([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value ?? 0,
                'total_price' => $total_price,
                'description' => $request->description,
                'image' => $imagePath,
            ]);

            // Auto-generate code
            $product->code = 'P' . $product->id;
            $product->save();

            return response()->json(['success' => true, 'product' => $product]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Other errors
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
