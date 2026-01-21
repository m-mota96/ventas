<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Store;

class StoreController extends Controller {
    public function index() {
        return Inertia::render('admin/Stores');
    }

    public function stores() {
        $stores = Store::select('id', 'name', 'address', 'status')
        ->where('user_id', auth()->user()->id)
        ->orderBy('id', 'DESC')
        ->get();

        return ResponseTrait::response(null, $stores);
    }

    public function saveStore(Request $request) {
        try {
            Store::create([
                'user_id'    => auth()->user()->id,
                'name'       => $request->name,
                'address'    => $request->address,
                'created_by' => auth()->user()->id
            ]);
            return ResponseTrait::response('La sucursal se registró correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function editStore(Request $request) {
        try {
            $store             = Store::find($request->id);
            $store->name       = $request->name;
            $store->address    = $request->address;
            $store->status     = $request->status;
            $store->updated_by = auth()->user()->id;
            $store->save();
            return ResponseTrait::response('La sucursal se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function deleteStore($id) {
        try {
            $store             = Store::find($id);
            $store->deleted_by = auth()->user()->id;
            $store->save();
            $store->delete();
            return ResponseTrait::response('La sucursal se eliminó correctamente.');
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
