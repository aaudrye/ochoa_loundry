<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Services\OrderService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(private OrderService $svc) {}

    /** Kurir hanya boleh membuka tugas aktif miliknya sendiri. */
    private function job(Order $order): Order
    {
        abort_unless($order->courier?->id === auth()->id(), 403);

        return $order;
    }

    public function home()
    {
        $jobs = Order::with('customer')->activeFor(auth()->id())->get();
        $done = OrderLog::where('user_id', auth()->id())->whereIn('status', [3, 10])->whereDate('created_at', today())
            ->get()->unique(fn ($l) => $l->order_id . '-' . $l->status)->count();

        return view('courier.home', compact('jobs', 'done'));
    }

    public function show(Order $order)
    {
        $order = $this->job($order)->load('customer', 'photos');

        return view('courier.show', compact('order'));
    }

    public function accept(Order $order)
    {
        $this->job($order);
        $order->status->value === 1
            ? $this->svc->acceptPickup($order, auth()->user())
            : $this->svc->acceptDelivery($order, auth()->user());

        return back();
    }

    public function arrive(Order $order)
    {
        $this->svc->arrive($this->job($order), auth()->user());

        return back();
    }

    public function weigh(Request $request, Order $order)
    {
        $kg = $request->validate(['weight_kg' => 'required|numeric|min:0.5|max:100'])['weight_kg'];
        $this->svc->weigh($this->job($order), auth()->user(), round((float) $kg, 1));

        return redirect()->route('courier.home')->with('ok', 'Hasil timbang & tagihan terkirim ke pelanggan.');
    }

    public function deliver(Order $order)
    {
        $this->svc->deliver($this->job($order), auth()->user());

        return redirect()->route('courier.home')->with('ok', 'Laundry diserahkan. Terima kasih!');
    }

    public function history()
    {
        $id = auth()->id();
        $pickups = Order::with('customer', 'logs')->where('pickup_courier_id', $id)->where('status', '>=', 3)->get()->map(fn ($o) => ['type' => 'Penjemputan', 'order' => $o]);
        $deliveries = Order::with('customer', 'logs')->where('delivery_courier_id', $id)->where('status', 10)->get()->map(fn ($o) => ['type' => 'Pengantaran', 'order' => $o]);
        $items = $pickups->concat($deliveries)->sortByDesc(fn ($i) => $i['order']->updated_at)->values();

        return view('courier.history', compact('items'));
    }
}