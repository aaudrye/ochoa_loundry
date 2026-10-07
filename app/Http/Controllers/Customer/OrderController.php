<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Midtrans\Config;
use Midtrans\CoreApi;

class OrderController extends Controller
{
    public function __construct(private OrderService $svc) {}

    private function mine(Order $order): Order
    {
        abort_unless($order->customer_id === auth()->id(), 403);

        return $order;
    }

    public function home()
    {
        $orders = Order::where('customer_id', auth()->id())->latest()->get();
        $notifs = OrderLog::with('order')->whereHas('order', fn ($q) => $q->where('customer_id', auth()->id()))
            ->latest('id')->limit(2)->get();

        return view('customer.home', compact('orders', 'notifs'));
    }

    public function create() { return view('customer.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service' => ['required', Rule::in(array_keys(config('ochoa.services')))],
            'color' => ['required', Rule::in(array_column(config('ochoa.colors'), 1))],
            'perfume' => ['required', Rule::in(array_column(config('ochoa.perfumes'), 1))],
            'category' => ['required', Rule::in(config('ochoa.categories'))],
            'note' => 'nullable|string|max:500',
            'pickup_address' => 'required|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => ['required', Rule::in(array_keys(config('ochoa.pickup_slots')))],
            'photos' => 'nullable|array|max:4',
            'photos.*' => 'image|max:4096',
        ]);

        $order = $this->svc->create([
            'customer_id' => auth()->id(),
            'price_per_kg' => config("ochoa.services.{$data['service']}.price"),
        ] + collect($data)->except('photos')->all());

        foreach ($request->file('photos', []) as $file) {
            $order->photos()->create(['path' => $file->store('orders', 'public')]);
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('ok', "Pesanan {$order->code} berhasil dikirim. Pembayaran dilakukan setelah kurir menimbang laundry.");
    }

    public function index()
    {
        $orders = Order::with(['pickupCourier', 'deliveryCourier'])
            ->where('customer_id', auth()->id())->latest()->get();

        return view('customer.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order = $this->mine($order)->load('photos', 'logs', 'pickupCourier', 'deliveryCourier');

        return view('customer.show', compact('order'));
    }

    public function payment(Order $order)
    {
        $this->mine($order);
        abort_unless($order->status->value === 3, 404);

        // --- MINTA QRIS DARI MIDTRANS ---
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $order->code . '-' . time(),
                'gross_amount' => (int) $order->total,
            ],
            'qris' => [
                'acquirer' => 'gopay'
            ]
        ];

        try {
            $response = CoreApi::charge($params);
            $qrUrl = $response->actions[0]->url ?? null;
        } catch (\Exception $e) {
            $qrUrl = null;
        }

        return view('customer.payment', compact('order', 'qrUrl'));
    }

    /**
     * Simulasi: tombol "Saya sudah bayar". Untuk produksi, ganti dengan webhook
     * payment gateway (Midtrans/Xendit QRIS) yang memanggil OrderService::pay().
     */
    public function pay(Order $order)
    {
        $this->svc->pay($this->mine($order), auth()->user());

        return redirect()->route('customer.orders.show', $order)->with('ok', 'Pembayaran diterima. Laundry masuk antrean cuci.');
    }

    public function slot(Request $request, Order $order)
    {
        $request->validate(['slot' => 'required|string']);
        $this->svc->setDeliverySlot($this->mine($order), $request->slot, auth()->user());

        return back()->with('ok', 'Jadwal pengantaran disimpan.');
    }
}