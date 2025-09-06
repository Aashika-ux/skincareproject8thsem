<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class ProductController extends Controller
{
    /**
     * List all products with recommended doctors
     */
    public function index()
    {
        
        $products = Product::with('recommendedByDoctors:id,name,specialization')->get();
        
            // Format products
        $products->transform(function ($product) {
            $product->image = $product->image
                ? url($product->image)
                : url('image/product/default.jpg');
            return $product;
        });

        

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get a single product by ID with recommended doctors
     */
    public function show($id)
    {
        $product = Product::with('recommendedByDoctors:id,name,specialization')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
  // ✅ Same image URL logic as index()
        $product->image = $product->image
            ? url($product->image)
            : url('image/product/default.jpg');

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }
public function search(Request $request)
{
    $query = $request->input('q'); // The keyword from frontend

    if (!$query) {
        return response()->json([
            'success' => false,
            'message' => 'Please enter a search term'
        ], 400);
    }

    $products = Product::with('recommendedByDoctors:id,name,specialization')
        ->where('name', 'LIKE', "%$query%")
        ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No products found'
        ], 404);
    }

    // ✅ Format images just like in index()
    $products->transform(function ($product) {
        $product->image = $product->image
            ? url($product->image)
            : url('image/product/default.jpg');
        return $product;
    });

    return response()->json([
        'success' => true,
        'data' => $products
    ]);
}



}
