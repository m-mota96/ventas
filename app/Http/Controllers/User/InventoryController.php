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
        $products = Product::select('id','name', 'bar_code', 'content', 'abreviation', 'type_sale')
        ->whereHas('productStore', function($q) {
            $q->where('status', 1)->where('store_id', auth()->user()->store_id);
        })
        ->orderBy('name')
        ->get();

        $references = Reference::select('*', 'id AS value', 'name AS label')->get();

        return Inertia::render('user/Inventory', [
            'products'   => $products,
            'references' => $references
        ]);
    }

    public function inventories(Request $request) {
        $pagination = $request->pagination;
        $page       = $pagination['currentPage']; // Página actual
        $limit      = $pagination['pageSize']; // Tamaño de la página
        $offset     = ($page - 1) * $limit; // Calcular el offset
        $search     = $request->search;

        $data = Inventory::with(['product:id,name,bar_code,content,abreviation,type_sale', 'createdBy:id,name', 'reference'])
        ->where('store_id', auth()->user()->store_id)
        ->where(function ($query) {
            $query->where('reference_id', '!=', 2)
            ->orWhere(function ($q) {
                $q->where('reference_id', 2)
                    ->whereHas('sale', function ($sale) {
                        $sale->where('status_id', 1); // Activa
                    });
            });
        });

        if ($search['product_name']) {
            $data->whereHas('product', function($q) use($search) {
                $q->whereLike('name', '%'.$search['product_name'].'%');
            });
        }
        if($search['quantity']) {
            $data->where('quantity',$search['quantity']);
        }
        if (isset($search['type'])) {
            $data->whereIn('type', $search['type']);
        }
        if (isset($search['reference'])) {
            $data->whereIn('reference_id', $search['reference']);
        }
        if (isset($search['date'])) {
            $search['date'][0] = $search['date'][0].' 00:00:00';
            $search['date'][1] = $search['date'][1].' 23:59:59';
            $data->whereBetween('created_at', $search['date']);
        }
        if ($search['created_by']) {
            $data->whereHas('createdBy', function($q) use($search) {
                $q->whereLike('name', '%'.$search['created_by'].'%');
            });
        }
        
        $inventories = $data->offset($offset)->limit($limit)->orderBy('id', 'DESC')->get();
        $totalRows   = $data->count();
        return ResponseTRait::response(null, ['inventories' => $inventories, 'totalRows' => $totalRows]);
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
                'price'           => ($request->type === 'input' && $request->reference === 1) ? $request->price : null,
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
