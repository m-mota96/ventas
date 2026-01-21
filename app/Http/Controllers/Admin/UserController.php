<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use App\Models\Store;
use App\Models\User;

class UserController extends Controller {
    public function index() {
        $stores = Store::select('id', 'name', 'address', 'status')
        ->where('user_id', auth()->user()->id)
        ->where('status', 1)
        ->orderBy('name')
        ->get();
        return Inertia::render('admin/Users', [
            'stores' => $stores
        ]);
    }

    public function users() {
        $users = User::with(['store:id,user_id,name'])
        ->select('id', 'store_id', 'name', 'email', 'status')
        ->whereHas('store', function($query) {
            $query->where('user_id', auth()->user()->id);
        })
        ->orderBy('id', 'DESC')
        ->get();

        return ResponseTrait::response(null, $users);
    }

    public function saveUser(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                return ResponseTrait::response(
                    'El correo electrónico ingresado ya esta registrado, intenta con otro.',
                    null,
                    true,
                    409
                );
            }
            $user = User::create([
                'store_id' => $request->store_id,
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole('user');
            return ResponseTrait::response('El usuario se registró correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function editUser(Request $request) {
        try {
            $user         = User::find($request->id);
            $user->name   = $request->name;
            $user->status = $request->status;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
            return ResponseTrait::response('El usuario se modificó correctamente.');
        } catch (\Throwable $th) {
            return ResponseTrait::response(
                'Lo sentimos ocurrio un error.<br>Si el problema persiste contacte a soporte.',
                $th->getMessage(),
                true,
                500
            );
        }
    }

    public function deleteUser($id) {
        try {
            $user = User::find($id);
            $user->delete();
            return ResponseTrait::response('El usuario se eliminó correctamente.');
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
