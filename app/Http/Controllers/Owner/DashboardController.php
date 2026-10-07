<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();
        $tx = Transaction::whereBetween('date', [$from->toDateString(), $to->toDateString()])->get();

        $income = $tx->where('type', 'in')->sum('amount');
        $expense = $tx->where('type', 'out')->sum('amount');
        $orders = Order::whereBetween('created_at', [$from, $to])->count();

        // pendapatan per minggu (1-4; hari 22+ masuk minggu ke-4)
        $weekly = collect(range(1, 4))->mapWithKeys(fn ($w) => [
            "Minggu $w" => $tx->where('type', 'in')->filter(fn ($t) => min(4, intdiv($t->date->day - 1, 7) + 1) === $w)->sum('amount'),
        ]);
        $max = max($weekly->max(), 1);

        return view('owner.dashboard', compact('income', 'expense', 'orders', 'weekly', 'max'));
    }
}