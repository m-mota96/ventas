<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Http\Traits\ResponseTrait;
use Carbon\Carbon;
use App\Models\Inventory;
use App\Models\Sale;

class StatisticController extends Controller {
    public function index() {
        return Inertia::render('user/Statistic');
    }

    public function statistics(Request $request) {
        $start_date     = Carbon::parse($request->year.'-'.$request->month.'-01');
        $month          = $request->month;
        $year           = $request->year;
        $end_day        = date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));
        $end_date       = Carbon::parse($end_day);
        $arraySales     = [];
        $arraySalesYear = [];

        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS sales')
            ->where('store_id', auth()->user()->store_id)
            ->whereDate('created_at', '=', $date->format('Y-m-d'))
            ->where('status_id', 1)
            ->first();
            $arraySales[$date->format('Y-m-d')] = floatval($sales->sales);
        }

        for ($i = 1; $i < 13; $i++) { 
            $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS sales')
            ->where('store_id', auth()->user()->store_id)
            ->whereYear('created_at', $request->currentYear)
            ->whereMonth('created_at', $i)
            ->where('status_id', 1)
            ->first();
            $month = $i < 10 ? '0'.$i : $i;
            $arraySalesYear[$request->currentYear.'-'.$month] = floatval($sales->sales);
        }

        $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS sales')
        ->where('store_id', auth()->user()->store_id)
        ->whereYear('created_at', $request->year)
        ->whereMonth('created_at', $request->month)
        ->where('status_id', 1)
        ->first();

        $expenses = Inventory::selectRaw('IF(SUM(price) IS NOT NULL, SUM(price), 0) AS expenses')
        ->where('store_id', auth()->user()->store_id)
        ->where('type', 'input') // Ingreso
        ->where('reference_id', 1) // Abastecimiento de producto
        ->whereYear('created_at', $request->year)
        ->whereMonth('created_at', $request->month)
        ->first();

        return ResponseTrait::response(null, [
            'sales'      => $arraySales,
            'totalSales' => $sales->sales,
            'expenses'   => $expenses->expenses,
            'salesYear'  => $arraySalesYear
        ]);
    }
}
