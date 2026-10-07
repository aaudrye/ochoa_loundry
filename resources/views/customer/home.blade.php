@extends('layouts.app')
@section('content')
<h1>Halo, {{ auth()->user()->name }} 👋</h1>
<p class="mu">Mau laundry hari ini? Kurir kami jemput dan timbang di rumah.</p>

{{-- Notifikasi Prototype --}}
@if ($orders->contains(fn ($o) => $o->status->value === 7))
    <div class="card" style="background:var(--soft);padding:12px 16px">📦 Laundry sudah selesai. Atur jadwal pengantaran.</div>
@endif

{{-- Statistik Card (Stack Vertikal) --}}
<div class="card">
    <div class="mu">Laundry Aktif</div>
    <div class="num">{{ $orders->filter(fn ($o) => $o->status->value < 10)->count() }}</div>
</div>

<div class="card">
    <div class="mu">Siap Diantar</div>
    <div class="num">{{ $orders->filter(fn ($o) => $o->status->value === 7)->count() }}</div>
</div>

<div class="card">
    <div class="mu">Selesai</div>
    <div class="num">{{ $orders->filter(fn ($o) => $o->status->value === 10)->count() }}</div>
</div>

{{-- Pesanan Terbaru --}}
<h3 style="margin-top:6px">Pesanan Terbaru</h3>
@forelse ($orders->take(3) as $o)
    <div class="card row" style="cursor:pointer" onclick="location.href='{{ route('customer.orders.show', $o) }}'">
        <div><b>#{{ $o->code }} · {{ ucfirst($o->service) }} {{ $o->weight_kg > 0 ? $o->weight_kg + 0 : '-' }} Kg</b>
            <div class="mu">{{ $o->status->label() }} · {{ $o->weight_kg > 0 ? 'Rp' . number_format($o->total, 0, ',', '.') : 'menunggu timbang' }}</div></div>
        @include('partials.badge', ['order' => $o])
    </div>
@empty
    <div class="card mu">Belum ada pesanan.</div>
@endforelse

{{-- Tombol Pesan Lagi --}}
<div class="card" style="background:linear-gradient(120deg,#082f55,#0ea5e9);color:#fff;border:0">
    <h3 style="color:#fff">Pesan Lagi</h3>
    <p style="margin:0 0 12px;opacity:.9">Nggak perlu datang ke outlet. Tinggal tentukan lokasi dan waktu jemput.</p>
    <a class="btn" style="background:#fff;color:var(--bd);text-decoration:none;display:inline-block" href="{{ route('customer.orders.create') }}">+ Pesan Laundry</a>
</div>
@endsection