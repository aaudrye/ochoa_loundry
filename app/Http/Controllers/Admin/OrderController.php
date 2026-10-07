<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $svc) {}

    public function incoming()
    {
        $orders = Order::with(['customer', 'photos', 'pickupCourier'])->where('status', '<=', 3)->latest()->get();

        return view('admin.incoming', compact('orders'));
    }

    public function assignForm(Order $order)
    {
        abort_unless(in_array($order->status->value, [0, 7]), 404);
        $couriers = User::where('role', 'kurir')->get();

        return view('admin.assign', compact('order', 'couriers'));
    }

    public function assign(Request $request, Order $order)
    {
        $courier = User::where('role', 'kurir')->findOrFail($request->validate(['courier_id' => 'required|integer'])['courier_id']);
        $isDelivery = $order->status->value === 7;
        $this->svc->assign($order, $courier, auth()->user());

        return redirect()->route($isDelivery ? 'admin.process' : 'admin.incoming')
            ->with('ok', "{$courier->name} menerima tugas " . ($isDelivery ? 'pengantaran.' : 'penjemputan.'));
    }

    public function process()
    {
        $orders = Order::with(['customer', 'pickupCourier', 'deliveryCourier'])->whereBetween('status', [4, 9])->latest()->get();

        return view('admin.process', compact('orders'));
    }

    public function advance(Order $order)
    {
        $this->svc->advance($order, auth()->user());

        return back()->with('ok', "Status #{$order->code} diperbarui.");
    }

    public function slot(Request $request, Order $order)
    {
        $request->validate(['slot' => 'required|string']);
        $this->svc->setDeliverySlot($order, $request->slot, auth()->user());

        return back()->with('ok', 'Jadwal pengantaran disimpan.');
    }

    public function notifications()
    {
        $logs = OrderLog::with('order.customer')->latest('id')->limit(50)->get();

        return view('admin.notifications', compact('logs'));
    }

    public function history()
    {
        $orders = Order::with(['customer', 'logs'])->latest()->get();

        return view('admin.history', compact('orders'));
    }
}