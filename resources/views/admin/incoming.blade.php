@extends('layouts.app')
@section('content')

<h1>Pesanan Masuk</h1>
<p class="mu">Tugaskan kurir free untuk menjemput & menimbang.</p>

<div class="card" style="overflow-x: auto;">
    <table style="width: 100%; min-width: 800px;">
        <thead>
            <tr style="border-bottom: 2px solid var(--ln);">
                <th style="text-align: left; padding: 12px;">ID</th>
                <th style="text-align: left; padding: 12px;">Pelanggan</th>
                <th style="text-align: left; padding: 12px;">Telepon</th>
                <th style="text-align: left; padding: 12px;">Alamat</th>
                <th style="text-align: left; padding: 12px;">Layanan</th>
                <th style="text-align: center; padding: 12px;">Berat</th>
                <th style="text-align: center; padding: 12px;">Status</th>
                <th style="text-align: center; padding: 12px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr style="border-bottom: 1px solid var(--ln);">
                    <td style="padding: 12px; font-weight: 700;">#{{ $order->code }}</td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;">{{ $order->customer->name }}</div>
                        <div class="mu" style="font-size: 11px;">{{ $order->customer->phone }}</div>
                    </td>
                    <td style="padding: 12px;">
                        <a href="tel:{{ preg_replace('/\D/', '', $order->customer->phone) }}" style="text-decoration: none;">
                            {{ $order->customer->phone }}
                        </a>
                    </td>
                    <td style="padding: 12px; max-width: 250px;">
                        <div style="font-size: 12px; line-height: 1.4;" title="{{ $order->pickup_address }}">
                            {{ Str::limit($order->pickup_address, 60) }}
                        </div>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600;">{{ ucfirst($order->service) }}</div>
                        <div class="mu" style="font-size: 11px;">@rp($order->price_per_kg)/kg</div>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        @if($order->weight_kg > 0)
                            <b>{{ $order->weight_kg + 0 }} kg</b>
                        @else
                            <span class="mu">-</span>
                        @endif
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        @include('partials.badge', ['order' => $order])
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        @if($order->status->value === 0)
                            <a class="btn s" style="text-decoration: none; white-space: nowrap;" href="{{ route('admin.assign.form', $order) }}">
                                Tugaskan Kurir
                            </a>
                        @elseif($order->courier)
                            @php
                                // Ambil nomor hp kurir (pilih field yang sesuai di model: phone / tel)
                                $courierPhone = $order->courier->phone ?? $order->courier->tel ?? '';
                                // Ubah format 08xx menjadi 628xx
                                $waNumber = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $courierPhone));
                                $textWA = urlencode("Halo {$order->courier->name}, ini admin Ochoa Laundry terkait pesanan #{$order->code}.");
                            @endphp

                            <div style="font-size: 11px; font-weight: 600;">
                                {{ $order->courier->name }}
                            </div>
                            <a class="btn s o" style="text-decoration: none; margin-top: 4px; display: inline-block;" href="https://wa.me/{{ $waNumber }}?text={{ $textWA }}" target="_blank">
                                Hubungi
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px;" class="mu">
                        Tidak ada pesanan baru
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection