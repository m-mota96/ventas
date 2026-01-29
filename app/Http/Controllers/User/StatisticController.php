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
        $start_date        = Carbon::parse($request->year.'-'.$request->month.'-01');
        $month             = $request->month;
        $year              = $request->year;
        $end_day           = date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));
        $end_date          = Carbon::parse($end_day);
        $arraySales        = [];
        $arraySalesYear    = [];
        $arrayExpenses     = [];
        $arrayExpensesYear = [];
        $storeId           = auth()->user()->store_id ?? $request->store_id;

        for($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS total')
            ->where('store_id', $storeId)
            ->whereDate('created_at', '=', $date->format('Y-m-d'))
            ->where('status_id', 1)
            ->first();
            $arraySales[$date->format('Y-m-d')] = floatval($sales->total);
            $expenses = Inventory::selectRaw('IF(SUM(price) IS NOT NULL, SUM(price), 0) AS total')
            ->where('store_id', $storeId)
            ->where('type', 'input') // Ingreso
            ->where('reference_id', 1) // Abastecimiento de producto
            ->whereDate('created_at', '=', $date->format('Y-m-d'))
            ->first();
            $arrayExpenses[$date->format('Y-m-d')] = floatval($expenses->total);
        }

        for ($i = 1; $i < 13; $i++) { 
            $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS total')
            ->where('store_id', $storeId)
            ->whereYear('created_at', $request->currentYear)
            ->whereMonth('created_at', $i)
            ->where('status_id', 1)
            ->first();
            $month = $i < 10 ? '0'.$i : $i;
            $arraySalesYear[$request->currentYear.'-'.$month] = floatval($sales->total);
        }

        $sales = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS total')
        ->where('store_id', $storeId)
        ->whereYear('created_at', $request->year)
        ->whereMonth('created_at', $request->month)
        ->where('status_id', 1)
        ->first();

        $expenses = Inventory::selectRaw('IF(SUM(price) IS NOT NULL, SUM(price), 0) AS total')
        ->where('store_id', $storeId)
        ->where('type', 'input') // Ingreso
        ->where('reference_id', 1) // Abastecimiento de producto
        ->whereYear('created_at', $request->year)
        ->whereMonth('created_at', $request->month)
        ->first();

        $salesCash = Sale::selectRaw('SUM(cash) AS total')
        ->where('store_id', $storeId)
        ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
        ->where('status_id', 1)
        ->first();

        $salesCard = Sale::selectRaw('SUM(card) AS total')
        ->where('store_id', $storeId)
        ->whereBetween('created_at', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
        ->where('status_id', 1)
        ->first();

        $salesForYear = Sale::selectRaw('IF(SUM(total) IS NOT NULL, SUM(total), 0) AS total')
        ->where('store_id', $storeId)
        ->whereYear('created_at', $request->currentYear)
        ->where('status_id', 1)
        ->first();

        $expensesForYear = Inventory::selectRaw('IF(SUM(price) IS NOT NULL, SUM(price), 0) AS total')
        ->where('store_id', $storeId)
        ->where('type', 'input') // Ingreso
        ->where('reference_id', 1) // Abastecimiento de producto
        ->whereYear('created_at', $request->currentYear)
        ->first();

        return ResponseTrait::response(null, [
            'sales'           => $arraySales,
            'totalSales'      => $sales->total,
            'expenses'        => $expenses->total,
            'salesYear'       => $arraySalesYear,
            'salesCash'       => floatval($salesCash->total),
            'salesCard'       => floatval($salesCard->total),
            'arrayExpenses'   => $arrayExpenses,
            'salesForYear'    => floatval($salesForYear->total),
            'expensesForYear' => floatval($expensesForYear->total)
        ]);
    }
}
