<?php

return [
    'services' => [
        'reguler' => ['label' => 'Reguler (2 hari)', 'price' => 7000,  'icon' => '🧺', 'note' => 'selesai 2 hari'],
        'express' => ['label' => 'Express (1 hari)', 'price' => 12000, 'icon' => '⚡', 'note' => 'selesai 1 hari'],
    ],
    'colors' => [
        ['🌈', 'Campur Warna', 'Pakaian putih & berwarna'],
        ['⚪', 'Putih', 'Khusus pakaian putih'],
        ['⚫', 'Hitam / Gelap', 'Khusus warna gelap'],
        ['🔵', 'Berwarna', 'Khusus pakaian berwarna'],
    ],
    'perfumes' => [
        ['🌸', 'Fresh Clean', 'Wangi bersih & segar'], ['💜', 'Lavender', 'Lembut & calming'],
        ['🌺', 'Sakura', 'Floral & fresh'], ['🌿', 'Green Tea', 'Segar & ringan'],
        ['🍋', 'Lemon Fresh', 'Citrus menyegarkan'], ['🌊', 'Ocean Breeze', 'Segar seperti udara laut'],
        ['🌹', 'Rose', 'Floral & manis'], ['🍨', 'Sweet Vanilla', 'Manis & lembut'],
        ['🚫', 'Tanpa Parfum', 'Tanpa pewangi tambahan'],
    ],
    'categories' => ['Pakaian Harian', 'Seragam', 'Selimut / Bedcover', 'Pakaian Bayi'],
    'pickup_slots' => ['08.00–10.00' => 'Pagi', '10.00–12.00' => 'Menjelang siang', '13.00–15.00' => 'Siang'],
    'delivery_slots' => [
        'Hari Ini' => ['15.00–17.00', '17.00–19.00'],
        'Besok'    => ['09.00–12.00', '13.00–15.00', '15.00–18.00'],
    ],
    'income_categories'  => ['Laundry', 'Lainnya'],
    'expense_categories' => ['Operasional', 'Bahan Baku', 'Transportasi', 'Listrik', 'Gaji', 'Lainnya'],

    // [label, route, [pola route aktif]]
    'nav' => [
        'customer' => [
            ['Home', 'customer.home', ['customer.home']],
            ['Pesan Laundry', 'customer.orders.create', ['customer.orders.create']],
            ['Pesanan Saya', 'customer.orders.index', ['customer.orders.index', 'customer.orders.show', 'customer.orders.payment']],
        ],
        'admin' => [
            ['Pesanan Masuk', 'admin.incoming', ['admin.incoming', 'admin.assign.form']],
            ['Proses Pesanan', 'admin.process', ['admin.process']],
            ['Notif', 'admin.notifications', ['admin.notifications']],
            ['Histori', 'admin.history', ['admin.history']],
        ],
        'kurir' => [
            ['Home', 'courier.home', ['courier.home', 'courier.show']],
            ['Riwayat Antar-Jemput', 'courier.history', ['courier.history']],
        ],
        'owner' => [
            ['Dashboard', 'owner.dashboard', ['owner.dashboard']],
            ['Keuangan', 'owner.finance', ['owner.finance']],
            ['Pengaturan Akun', 'owner.account', ['owner.account']],
        ],
    ],
];