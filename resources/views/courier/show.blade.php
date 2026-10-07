@extends('layouts.app')
@section('title', 'Detail Tugas Kurir')
@section('content')

@php
    $pickup = $order->status->value <= 2;
@endphp

<a class="btn s l" style="text-decoration: none; margin-bottom: 16px; display: inline-block;" href="{{ route('courier.home') }}">← Kembali</a>

{{-- Card Utama: Info Pesanan & Pelanggan --}}
<div class="card">
    <div class="row" style="align-items: center; margin-bottom: 16px;">
        <h1 style="margin: 0; font-size: 20px; flex: 1;">#{{ $order->code }} · {{ $pickup ? 'Penjemputan' : 'Pengantaran' }}</h1>
        @include('partials.badge', ['order' => $order])
    </div>

    {{-- Info Pelanggan --}}
<div style="margin-bottom: 16px;">
    @php
        // Ambil nomor hp pelanggan dan bersihkan karakter non-angka
        $custPhone = preg_replace('/[^0-9]/', '', $order->customer->phone);
        // Ubah format 08xx menjadi 628xx
        $waNumber = preg_replace('/^0/', '62', $custPhone);
        $textWA = urlencode("Halo Kak {$order->customer->name}, saya kurir Ochoa Laundry mau menjemput pesanan #{$order->code}. Apakah Kakak sedang ada di rumah?");
    @endphp

    <div class="row" style="align-items: center; margin-bottom: 8px;">
        <b style="font-size: 16px; color: var(--navy);">{{ $order->customer->name }}</b>
        <a href="https://wa.me/{{ $waNumber }}?text={{ $textWA }}" 
           target="_blank" 
           class="btn s o" 
           style="text-decoration: none; margin-left: 8px; border-radius: 99px;">
            Hubungi
        </a>
    </div>
    <div style="color: var(--tx); font-size: 14px; margin-bottom: 4px;">
        {{ $order->customer->phone }}
    </div>
    <div style="color: var(--tx); font-size: 14px;">
        📍 {{ $order->pickup_address }}
        @if($order->lat && $order->lng)
            · <a href="https://www.google.com/maps/search/?api=1&query={{ $order->lat }},{{ $order->lng }}" 
                 target="_blank" 
                 style="color: var(--b); text-decoration: none;">
                🗺️ Buka di Google Maps
            </a>
        @endif
    </div>
</div>

    {{-- Detail Pesanan (hanya untuk penjemputan) --}}
    @if($pickup)
        <div style="border-top: 1px solid var(--ln); padding-top: 16px; margin-top: 16px;">
            <div style="font-size: 13px; color: var(--mu); line-height: 1.8;">
                <div>Layanan: {{ $order->service_label }}</div>
                <div>Warna: {{ $order->color }} · Parfum: {{ $order->perfume }} · {{ $order->category }}</div>
                <div>Jadwal: {{ $order->pickup_date->format('d/m/Y') }} · {{ $order->pickup_time }}</div>
                <div>Catatan: {{ $order->note ?: '-' }}</div>
            </div>
        </div>
    @else
        <div style="border-top: 1px solid var(--ln); padding-top: 16px; margin-top: 16px;">
            <div style="font-size: 13px; color: var(--mu);">
                Jadwal antar: {{ $order->delivery_slot }} · Total @rp($order->total) (lunas)
            </div>
        </div>
    @endif

    {{-- Foto Laundry --}}
    @if($pickup && $order->photos->count())
        <div style="border-top: 1px solid var(--ln); padding-top: 16px; margin-top: 16px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @foreach($order->photos as $photo)
                    <img src="{{ $photo->url }}" alt="Foto laundry" style="width: 64px; height: 64px; object-fit: cover; border-radius: 10px;">
                @endforeach
            </div>
        </div>
    @endif
</div>

{{-- Tombol Aksi Berdasarkan Status --}}
@switch($order->status->value)
    @case(1)
    @case(8)
        <form method="POST" action="{{ route('courier.accept', $order) }}">
            @csrf
            <button type="submit" class="btn w" style="padding: 16px; font-size: 16px;">
                Terima Tugas & Berangkat{{ $order->status->value === 8 ? ' Antar' : '' }}
            </button>
        </form>
        @break

    @case(2)
        @if(!$order->arrived)
            <form method="POST" action="{{ route('courier.arrive', $order) }}">
                @csrf
                <button type="submit" class="btn w" style="padding: 16px; font-size: 16px;">
                    📍 Saya Sudah Sampai
                </button>
            </form>
        @else
            {{-- Section Timbang --}}
            <div class="card">
                <h3 style="margin-bottom: 16px;">⚖️ Timbang dengan timbangan digital</h3>
                <form method="POST" action="{{ route('courier.weigh', $order) }}">
                    @csrf
                    <div class="f">
                        <label style="display: block; margin-bottom: 8px; font-weight: 700; color: var(--navy);">Berat (kg)</label>
                        <input type="number" 
                               name="weight_kg" 
                               id="kg" 
                               step="0.1" 
                               min="0.5" 
                               placeholder="0.5" 
                               required 
                               style="width: 100%; padding: 14px; border: 1.5px solid var(--ln); border-radius: 12px; font-size: 16px; box-sizing: border-box;">
                    </div>
                    <div style="margin: 16px 0;">
                        <span style="color: var(--mu); font-size: 14px;">Tagihan: </span>
                        <b style="font-size: 20px; color: var(--navy);" id="est">Rp0</b>
                    </div>
                    <button type="submit" class="btn w" style="padding: 16px; font-size: 16px;">
                        Kirim Hasil Timbang & Tagihan
                    </button>
                </form>
            </div>
        @endif
        @break

    @case(9)
        @if(!$order->arrived)
            <form method="POST" action="{{ route('courier.arrive', $order) }}">
                @csrf
                <button type="submit" class="btn w" style="padding: 16px; font-size: 16px;">
                    📍 Saya Sudah Sampai
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('courier.deliver', $order) }}">
                @csrf
                <button type="submit" class="btn w" style="padding: 16px; font-size: 16px;">
                    ✅ Laundry Diserahkan
                </button>
            </form>
        @endif
        @break

    @default
        <div class="card" style="background: var(--soft); text-align: center;">
            <p class="mu">Tidak ada aksi yang tersedia untuk status ini.</p>
        </div>
@endswitch

@endsection

@push('scripts')
@if($order->status->value === 2 && $order->arrived)
<script>
    const price = {{ $order->price_per_kg }};
    document.getElementById('kg').addEventListener('input', function(e) {
        const weight = parseFloat(e.target.value) || 0;
        const total = Math.round(weight * price);
        document.getElementById('est').textContent = 'Rp' + total.toLocaleString('id-ID');
    });
</script>
@endif
@endpush