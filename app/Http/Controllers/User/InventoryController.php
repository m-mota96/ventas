<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Reference;

class InventoryController extends Controller {
    public function index() {
        $products = Product::select('id','name', 'bar_code', 'content', 'abreviation')
        ->whereHas('productStore', function($q) {
            $q->where('status', 1)->where('store_id', auth()->user()->store_id);
        })
        ->orderBy('name')
        ->get();

        $references = Reference::whereNotIn('id', [2])->orderBy('name')->get();

        return Inertia::render('user/Inventory', [
            'products'   => $products,
            'references' => $references
        ]);
    }

    public function inventories(Request $request) {
        $inventories = Inventory::with(['product:id,name,bar_code,content,abreviation', 'createdBy:id,name', 'reference'])
        ->where('store_id', auth()->user()->store_id)
        ->orderBy('id', 'DESC')
        ->get();

        return ResponseTRait::response(null, $inventories);
    }

    public function saveInventory(Request $request) {
        try {
            Inventory::create([
                'product_id'      => $request->product_id,
                'store_id'        => auth()->user()->store_id,
                'reference_id'    => $request->reference,
                'type'            => $request->type,
                'batch'           => $request->batch,
                'expiration_date' => $request->expiration_date,
                'quantity'        => $request->quantity,
                'description'     => $request->description,
                'created_by'      => auth()->user()->id
            ]);
            return ResponseTrait::response('El registro se guardó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function editInventory(Request $request) {
        try {
            
            return ResponseTrait::response('El registro se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function deleteInventory($id) {
        try {
            $inventory             = Inventory::where('id', $id)->where('store_id', auth()->user()->store_id)->first();
            $inventory->deleted_by = auth()->user()->id;
            $inventory->save();
            $inventory->delete();
            return ResponseTrait::response('El registro se eliminó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }
}
