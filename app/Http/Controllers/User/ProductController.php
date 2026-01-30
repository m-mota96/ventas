<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductStore;

class ProductController extends Controller {
    public function index() {
        return Inertia::render('user/Products');
    }

    public function products(Request $request) {
        $pagination = $request->pagination;
        $page       = $pagination['currentPage']; // Página actual
        $limit      = $pagination['pageSize']; // Tamaño de la página
        $offset     = ($page - 1) * $limit; // Calcular el offset

        $data = Product::with(['productStore'])
        ->addSelect(['inputs' => Inventory::selectRaw('IF(SUM(quantity) IS NULL, 0, SUM(quantity)) as quantity')
            ->whereColumn('product_id', 'products.id')
            ->where('type', 'input')
            ->groupBy('product_id')
        ])
        ->addSelect(['outputs' => Inventory::selectRaw('IF(SUM(quantity) IS NULL, 0, SUM(quantity)) as quantity')
            ->whereColumn('product_id', 'products.id')
            ->where('type', 'output')
            ->where(function ($query) {
                $query->where('reference_id', '!=', 2)
                ->orWhere(function ($q) {
                    $q->where('reference_id', 2)
                    ->whereHas('sale', function ($sale) {
                        $sale->where('status_id', 1); // Activa
                    });
                });
            })
            ->groupBy('product_id')
        ])
        ->whereHas('productStore', function($q) {
            $q->where('store_id', auth()->user()->store_id);
        });
        // ->offset($offset)->limit($limit)
        // ->orderBy('name')
        // ->get();

        $products  = $data->offset($offset)->limit($limit)->orderBy('name')->get();
        $totalRows = $data->count();
        return ResponseTrait::response(null, ['products' => $products, 'totalRows' => $totalRows]);
    }

    public function saveProduct(Request $request) {
        try {
            // dd('create', $request->all());
            $product = Product::where('bar_code', $request->bar_code)
            // ->whereHas('productStore', function($q) {
            //     $q->where('')
            // })
            ->first();
            if ($product) {
                return ResponseTrait::response(
                    'El <b>código de barras/Clave</b> que ingresaste ya esta registrado.<br>Por favor verifica tus productos.',
                    null,
                    true,
                    409
                );
            }
            $product = Product::create([
                'name'        => $request->name,
                'bar_code'    => $request->bar_code,
                'content'     => $request->content,
                'abreviation' => $request->abreviation,
                'type_sale'   => $request->type_sale,
                'description' => $request->description,
                'created_by'  => auth()->user()->id
            ]);
            ProductStore::create([
                'product_id'       => $product->id,
                'store_id'         => auth()->user()->store_id,
                'price'            => $request->price,
                'batch'            => $request->batch,
                'expiration_date'  => $request->expiration_date,
                'discounted_price' => $request->discounted_price,
                'special_price'    => $request->special_price,
                'created_by'       => auth()->user()->id
            ]);
            return ResponseTrait::response('El producto se registró correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function editProduct(Request $request) {
        try {
            // dd('edit', $request->all());
            $product              = Product::find($request->id);
            $product->name        = $request->name;
            $product->bar_code    = $request->bar_code;
            $product->content     = $request->content;
            $product->abreviation = $request->abreviation;
            $product->type_sale   = $request->type_sale;
            $product->description = $request->description;
            $product->updated_by  = auth()->user()->id;
            $product->save();
            
            $productStore                   = ProductStore::where('product_id', $product->id)->where('store_id', auth()->user()->store_id)->first();
            if ($productStore) {
                $productStore->price            = $request->price;
                $productStore->discounted_price = $request->discounted_price;
                $productStore->special_price    = $request->special_price;
                $productStore->status           = $request->status;
                $productStore->updated_by       = auth()->user()->id;
                $productStore->save();
            } else {
                ProductStore::create([
                    'product_id'       => $product->id,
                    'store_id'         => auth()->user()->store_id,
                    'price'            => $request->price,
                    'batch'            => $request->batch,
                    'expiration_date'  => $request->expiration_date,
                    'discounted_price' => $request->discounted_price,
                    'special_price'    => $request->special_price,
                    'created_by'       => auth()->user()->id
                ]);
            }
            return ResponseTrait::response('El producto se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function deleteProduct($id) {
        try {
            $productStore             = ProductStore::where('product_id', $id)->where('store_id', auth()->user()->store_id)->first();
            $productStore->deleted_by = auth()->user()->id;
            $productStore->save();
            $productStore->delete();
            return ResponseTrait::response('El producto se eliminó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function product(Request $request) {
        $query = Product::with(['productStore:id,product_id,store_id,price,discounted_price,special_price,status'])
        ->select(
            'id',
            'name',
            'content',
            'abreviation',
            'type_sale',
            'description'
        );
        if ($request->type === 'sale') { // Si esta vendiendo productos buscamos los que tiene registrados en esa sucursal
            $query->whereHas('productStore', function($q) {
                $q->where('status', 1)->where('store_id', auth()->user()->store_id);
            });
        } else { // Si va a registrar un producto, buscamos en los que tienen otras sucursales para obtener la información
            $query->whereHas('productStore', function($q) {
                $q->whereNotIn('store_id', [auth()->user()->store_id]);
            });
        }
        $query->whereLike('name', '%'.$request->name.'%');
        $products = $query->orderBy('name', 'ASC')->get();
        return ResponseTrait::response(null, $products);
    }
}
