<?php

namespace App\Http\Controllers;

use App\Models\ProductService;
use Illuminate\Http\Request;

class ApiExternalController extends Controller
{

    public function get_products()
    {
        $productos = ProductService::all();

        return response()->json([
            'success' => true,
            'data' => $productos,
        ]);
    }

    public function update_product_bySku($sku, Request $request)
    {
        
        $producto = ProductService::where('sku', $sku)->first();

        if (!$producto) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if (!$request->has('quantity')) {
            return response()->json(['success' => false, 'message' => 'Quantity field is required'], 422);
        }


        $producto->quantity = $request->input('quantity');
        $producto->save();

        return response()->json([
            'success' => true,
            'data' => $producto,
        ]);
    }
}
