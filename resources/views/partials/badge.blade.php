@php
    // Tentukan warna badge berdasarkan status pesanan
    $colors = [
        0 => 'o1', // Menunggu Kurir (Orange)
        1 => 'o1', // Kurir Ditugaskan
        2 => 'o1', // Menuju Lokasi
        3 => 'o1', // Menunggu Pembayaran
        4 => 'g1', // Cucian Masuk (Green)
        5 => 'g1', // Pencucian
        6 => 'g1', // Setrika
        7 => 'g1', // Siap Antar
        8 => 'o1', // Kurir Antar Ditugaskan
        9 => 'o1', // Sedang Diantar
        10 => 'g1', // Selesai
    ];
    
    $class = $colors[$order->status->value] ?? 'o1';
@endphp

<span class="bd {{ $class }}">{{ $order->status->label() }}</span>