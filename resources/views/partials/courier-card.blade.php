@php
    $k = $order->courier;
@endphp

@if ($k)
    @php
        $courierPhone = $k->phone ?? $k->tel ?? '';
        $cleanPhone = preg_replace('/[^0-9]/', '', $courierPhone);
        $waNumber = preg_replace('/^0/', '62', $cleanPhone);
        $textWA = urlencode("Halo {$k->name}, saya pelanggan pesanan #{$order->code}. Ingin menanyakan posisi/jadwal laundry saya.");
    @endphp

    <div class="card" style="background:var(--soft)">
        🚚 <b>{{ $order->status->value === 2 ? 'Kurir sedang menuju lokasi' : ($order->status->value === 9 ? 'Kurir sedang mengantar laundry kamu' : 'Kurir ditugaskan') }}</b>
        <div class="row" style="margin:10px 0;align-items:flex-start">
            <div>
                <b style="font-size:16px;color:var(--navy)">{{ $k->name }}</b>
                <div class="mu">🏍️ {{ $k->vehicle }}</div>
                <div style="margin-top:4px">📞 <b>{{ $courierPhone }}</b></div>
            </div>
        </div>
        <div class="g g2">
            <a class="btn s w" style="text-align:center;text-decoration:none" target="_blank" href="https://wa.me/{{ $waNumber }}?text={{ $textWA }}">
                📞 Hubungi Kurir
            </a>
            <a class="btn s o w" style="text-align:center;text-decoration:none" target="_blank" href="https://wa.me/{{ $waNumber }}?text={{ $textWA }}">
                💬 WhatsApp
            </a>
        </div>
    </div>
@endif