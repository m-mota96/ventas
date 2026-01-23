<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Inventory;
use App\Models\Sale;

class SaleController extends Controller {
    public function index() {
        return Inertia::render('user/Sales');
    }

    public function sales(Request $request) {
        $pagination = $request->pagination;
        $page       = $pagination['currentPage']; // Página actual
        $limit      = $pagination['pageSize']; // Tamaño de la página
        $offset     = ($page - 1) * $limit; // Calcular el offset

        $data = Sale::with(['paymentMethod', 'inventories.product', 'status', 'createdBy', 'updatedBy'])
        ->where('store_id', auth()->user()->store_id);

        $sales     = $data->offset($offset)->limit($limit)->orderBy('id', 'DESC')->get();
        $totalRows = $data->count();
        return ResponseTrait::response(null, ['sales' => $sales, 'totalRows' => $totalRows]);
    }

    public function registerSale(Request $request) {
        try {
            $saleReq  = $request->sale;
            $products = $request->products;
            $sale = Sale::create([
                'store_id'          => auth()->user()->store_id,
                'status_id'         => 1,
                'payment_method_id' => $saleReq['paymentMethod'],
                'cash'              => $saleReq['paymentMethod'] === 3 ? $saleReq['cash'] : ($saleReq['paymentMethod'] === 1 ? $saleReq['total'] : null),
                'card'              => $saleReq['paymentMethod'] === 3 ? $saleReq['card'] : ($saleReq['paymentMethod'] === 2 ? $saleReq['total'] : null),
                'total'             => $saleReq['total'],
                'created_by'        => auth()->user()->id
            ]);
            foreach ($products as $key => $p) {
                $insert[] = [
                    'product_id'   => $p['id'],
                    'store_id'     => auth()->user()->store_id,
                    'reference_id' => 2,
                    'sale_id'      => $sale->id,
                    'type'         => 'output',
                    'quantity'     => $p['quantity'],
                    'price'        => $p[$p['price_applied']],
                    'discount'     => $p['price_applied'],
                    'created_by'   => auth()->user()->id,
                    'created_at'   => date('Y-m-d H:i:s'),
                    'updated_at'   => date('Y-m-d H:i:s'),
                ];
            }
            Inventory::insert($insert);
            return ResponseTrait::response('La venta se registró correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function editSale(Request $request) {
        try {
            $sale               = Sale::where('id', $request->id)->where('store_id', auth()->user()->store_id)->first();
            $sale->status_id    = $request->status;
            $sale->observations = $request->observations;
            $sale->updated_by   = auth()->user()->id;
            $sale->save();
            return ResponseTrait::response('La venta de modificó correctamente.');
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
