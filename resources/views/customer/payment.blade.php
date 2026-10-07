@extends('layouts.app')
@section('content')
<div class="auth" style="margin-top:10px">
    <div class="card" style="text-align:center">
        <h3>Pembayaran #{{ $order->code }}</h3>
        <p class="mu">Berat hasil timbang digital kurir: <b>{{ $order->weight_kg + 0 }} kg</b> × @rp($order->price_per_kg)</p>
        <div class="num">@rp($order->total)</div>
        
        {{-- QRIS Dinamis dari Midtrans --}}
        <div style="margin:16px auto; display:flex; justify-content:center;">
            @if(isset($qrUrl) && $qrUrl)
                <img src="{{ $qrUrl }}" alt="QRIS Code" style="width:176px; height:176px; border-radius:12px; border:1px solid var(--ln); object-fit:contain;">
            @else
                {{-- Fallback jika API Midtrans offline / key error --}}
                <img src="https://api.sandbox.midtrans.com/v2/qris/{{ $order->code }}/qr-code" 
                    alt="QRIS Code" 
                    style="width:176px; height:176px; border-radius:12px; border:1px solid var(--ln); object-fit:contain;">
            @endif
        </div>

        <p class="mu">Scan QRIS (atau bayar lewat QRIS kurir)</p>
        <form method="POST" action="{{ route('customer.orders.pay', $order) }}">@csrf
            <button class="btn w">Saya Sudah Bayar</button>
        </form>
        <a class="btn w l" style="display:block;margin-top:6px;text-decoration:none" href="{{ route('customer.orders.show', $order) }}">Batal</a>
    </div>
</div>
@endsection