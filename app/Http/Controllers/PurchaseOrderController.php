<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Bill;

class PurchaseOrderController extends Controller
{
    public function byVendorAndOrder(Request $request)
    {
        $data = $request->validate([
            'vendor_id'    => ['required','integer'],      // ID del proveedor (tabla venders)
            'order_number' => ['required','string'],       // Número de orden/compra (campo en bills)
        ]);

        $bill = Bill::with(['items.product:id,name,description'])
            ->where('vender_id', $data['vendor_id'])
            ->where('order_number', $data['order_number']) // ajusta si tu campo se llama distinto
            ->first();

        if (!$bill) {
            return response()->json([
                'message' => 'No se encontró la orden para ese proveedor y número.',
            ], 404);
        }

        $products = $bill->items->map(function ($item) {
            $desc = $item->product?->description ?? $item->product?->name ?? 'Producto';
            return [
                'description'        => $desc,
                'quantity'           => (float)($item->quantity ?? 0),
                'received_quantity'  => 0,   // siempre 0
            ];
        })->values();

        return response()->json([
            'supplier'     => ['id' => $bill->vender_id, 'name' => $bill->vender?->name ?? 'Proveedor'],
            'order_number' => $bill->order_number,
            'products'     => $products,
        ]);
    }

}
