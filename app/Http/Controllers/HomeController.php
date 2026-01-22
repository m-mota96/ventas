<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PaymentMethod;
use App\Models\Inventory;
use App\Models\Product;

class HomeController extends Controller {
    public function index() {
        switch (auth()->user()->getRoleNames()[0]) {
            case 'admin':
                return redirect(route('administrador.inicio'));
            break;
            case 'user':
                return redirect(route('usuario.inicio'));
            break;
        }
    }

    public function stores() {
        return Inertia::render('admin/Home');
    }

    public function users() {
        $products = Product::with(['productStore:product_id,store_id,price,discounted_price,special_price,status'])
        ->select(
            'id',
            'name',
            'content',
            'abreviation',
            'type_sale',
            'description'
        )
        ->addSelect([
            'stock' => Inventory::selectRaw("
                COALESCE(SUM(
                    CASE 
                        WHEN type = 'input' THEN quantity
                        WHEN type = 'output' THEN -quantity
                        ELSE 0
                    END
                ), 0)
            ")
            ->whereColumn('product_id', 'products.id')
        ])
        ->whereHas('productStore', function($q) {
            $q->where('status', 1)->where('store_id', auth()->user()->store_id);
        })
        ->orderBy('name')
        ->get();

        $paymentMethods = PaymentMethod::where('status', 1)->orderBy('payment_method')->get();
        return Inertia::render('user/Home', [
            'listProducts'   => $products,
            'paymentMethods' => $paymentMethods
        ]);
    }
}
