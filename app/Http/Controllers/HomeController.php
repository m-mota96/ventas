<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PaymentMethod;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Store;

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
        $stores = Store::select('id', 'user_id', 'name')->where('user_id', auth()->user()->id)->orderBy('name')->get();
        return Inertia::render('user/Statistic', [
            'stores' => $stores
        ]);
    }

    public function users() {
        $paymentMethods = PaymentMethod::where('status', 1)->orderBy('payment_method')->get();
        return Inertia::render('user/Home', [
            'paymentMethods' => $paymentMethods
        ]);
    }
}
