@extends('layouts.app')
@section('title', 'Detail Pesanan')
@section('content')

<a class="btn s l" style="text-decoration:none; margin-bottom: 16px; display: inline-block;" href="{{ route('customer.orders.index') }}">← Kembali</a>

{{-- 1. Header & Badge Status --}}
<div class="card">
    <div class="row" style="align-items: flex-start; margin-bottom: 16px;">
        <div>
            <h1 style="margin: 0; font-size: 20px;">#{{ $order->code }}</h1>
            <p class="mu" style="margin: 4px 0 0;">{{ $order->service_label }} · @if($order->weight_kg > 0){{ $order->weight_kg + 0 }} kg · @rp($order->total)@else berat ditimbang kurir @endif</p>
        </div>
        @include('partials.badge', ['order' => $order])
    </div>

    {{-- 2. Progress Stepper --}}
    <div style="margin: 24px 0;">
        <div class="stepper" style="margin: 0;">
            @foreach(['Dijemput', 'Payment', 'Cuci', 'Siap', 'Antar'] as $index => $label)
                @php
                    $step = $index + 1;
                    $isActive = $step <= $order->status->progress();
                @endphp
                <div class="step-item {{ $isActive ? 'done' : '' }}" style="position: relative; z-index: 1; text-align: center; flex: 1;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $isActive ? 'var(--b)' : 'var(--ln)' }}; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; color: {{ $isActive ? '#fff' : 'var(--mu)' }}; font-weight: 700; font-size: 12px;">
                        {{ $isActive ? '✓' : $step }}
                    </div>
                    <div style="font-size: 11px; color: {{ $isActive ? 'var(--bd)' : 'var(--mu)' }}; font-weight: 600;">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- 3. Info Kurir (jika sudah ditugaskan) --}}
@if($order->courier)
    @include('partials.courier-card', ['order' => $order])
@endif

{{-- 4. Aksi Berdasarkan Status --}}
@if($order->status->value === 3)
    <div class="card" style="margin-top: 16px; background: var(--orb); border-color: var(--or);">
        <p style="margin: 0 0 12px; font-weight: 700; color: var(--or);">💳 Laundry sudah ditimbang. Segera lakukan pembayaran!</p>
        <a class="btn w" style="display: block; text-align: center; text-decoration: none;" href="{{ route('customer.orders.payment', $order) }}">Bayar Sekarang @rp($order->total)</a>
    </div>
@elseif($order->status->value === 7 && !$order->delivery_slot)
    <div class="card" style="margin-top: 16px; background: var(--soft);" id="jadwal">
        <h3>Jadwal Pengantaran</h3>
        <form method="POST" action="{{ route('customer.orders.slot', $order) }}">@csrf
            @foreach (config('ochoa.delivery_slots') as $day => $times)
                <div class="lb">{{ $day }}</div>
                <div class="og">
                    @foreach ($times as $t)
                        <label class="op"><input type="radio" name="slot" value="{{ $day }} {{ $t }}" required><b style="display:inline">{{ $t }}</b></label>
                    @endforeach
                </div>
            @endforeach
            <button class="btn w" style="margin-top: 12px;">Simpan Jadwal</button>
        </form>
    </div>
@endif

{{-- 5. Detail Pesanan --}}
<div class="card" style="margin-top: 16px;">
    <h3 style="margin-bottom: 16px;">Detail Pesanan</h3>
    <div style="display: grid; gap: 12px;">
        @foreach ([
            'Layanan' => $order->service_label,
            'Berat' => $order->weight_kg > 0 ? ($order->weight_kg + 0) . ' Kg' : 'Ditimbang kurir',
            'Total' => $order->weight_kg > 0 ? 'Rp' . number_format($order->total, 0, ',', '.') : 'Dihitung setelah ditimbang',
            'Warna / Parfum' => $order->color . ' / ' . $order->perfume,
            'Kategori' => $order->category,
            'Catatan' => $order->note ?: 'Tidak ada',
        ] as $k => $v)
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--ln);">
                <span class="mu">{{ $k }}</span>
                <b style="color: var(--navy); text-align: right; max-width: 60%;">{{ $v }}</b>
            </div>
        @endforeach
        
      @if ($order->photos->count())
            <div style="margin-top: 12px;">
                <span class="mu" style="display: block; margin-bottom: 8px;">Foto Laundry</span>
                <div class="ph">
                    @foreach ($order->photos as $p)
                        <img src="{{ $p->url }}" alt="foto laundry" style="width: 64px; height: 64px; object-fit: cover; border-radius: 10px;">
                    @endforeach
                </div>
            </div>
       @endif
    </div>
</div>

{{-- 6. Detail Penjemputan --}}
<div class="card" style="margin-top: 16px;">
    <h3 style="margin-bottom: 16px;">Detail Penjemputan</h3>
    <div style="display: grid; gap: 12px;">
        @foreach ([
            'Alamat' => $order->pickup_address,
            'Tanggal & Waktu' => $order->pickup_date->format('d/m/Y') . ' · ' . $order->pickup_time,
            'Kurir Penjemput' => $order->pickupCourier?->name ?? 'Belum ditugaskan',
            'Kurir Pengantar' => $order->deliveryCourier?->name ?? '-',
            'Jadwal Antar' => $order->delivery_slot ?? '-',
        ] as $k => $v)
            <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--ln);">
                <span class="mu">{{ $k }}</span>
                <b style="color: var(--navy); text-align: right; max-width: 60%;">{{ $v }}</b>
            </div>
        @endforeach
    </div>
</div>

{{-- 7. Status Perjalanan (Timeline) --}}
<div class="card" style="margin-top: 16px;">
    <h3 style="margin-bottom: 16px;">Status Perjalanan</h3>
    @include('partials.timeline', ['order' => $order])
</div>

@endsection

@push('head')
<style>
.stepper { display: flex; justify-content: space-between; position: relative; }
.stepper::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 2px; background: var(--ln); z-index: 0; }
.step-item { position: relative; z-index: 1; text-align: center; flex: 1; }
</style>
@endpush