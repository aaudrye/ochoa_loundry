<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // password semua akun demo: "password"
        $aisha = User::create(['name' => 'Aisha', 'email' => 'aisha@gmail.com', 'password' => 'password', 'role' => 'customer', 'phone' => '0812-3456-7890', 'address' => 'Jl. Contoh Raya No. 12, Pamulang']);
        User::create(['name' => 'Trista', 'email' => 'trista@gmail.com', 'password' => 'password', 'role' => 'customer', 'phone' => '0813-1111-2222']);
        User::create(['name' => 'Syabila', 'email' => 'syabila@gmail.com', 'password' => 'password', 'role' => 'admin', 'phone' => '0812-0000-0001']);
        User::create(['name' => 'Gina', 'email' => 'gina@gmail.com', 'password' => 'password', 'role' => 'admin', 'phone' => '0812-0000-0002']);
        User::create(['name' => 'Kurir A', 'email' => 'kurira@gmail.com', 'password' => 'password', 'role' => 'kurir', 'phone' => '0821-1111-2201', 'vehicle' => 'Honda Beat · B 1234 XYZ']);
        User::create(['name' => 'Kurir B', 'email' => 'kurirb@gmail.com', 'password' => 'password', 'role' => 'kurir', 'phone' => '0821-1111-2202', 'vehicle' => 'Yamaha Mio · B 5678 ABC']);
        User::create(['name' => 'Kurir C', 'email' => 'kurirc@gmail.com', 'password' => 'password', 'role' => 'kurir', 'phone' => '0821-1111-2203', 'vehicle' => 'Honda Vario · B 9012 DEF']);
        User::create(['name' => 'Owner OCHOA', 'email' => 'owner@gmail.com', 'password' => 'password', 'role' => 'owner', 'phone' => '0812-0000-0000']);

        foreach ([['Kurir A', 'Kurir', 3000000, 300000], ['Kurir B', 'Kurir', 3000000, 0], ['Setrika A', 'Staff', 2500000, 200000]] as [$n, $p, $s, $o]) {
            Employee::create(['name' => $n, 'position' => $p, 'salary' => $s, 'overtime' => $o]);
        }

        foreach ([
            ['2026-09-05', 'in', 'Laundry', 'Pendapatan laundry', 2500000],
            ['2026-09-06', 'in', 'Laundry', 'Pendapatan laundry', 1850000],
            ['2026-09-07', 'out', 'Operasional', 'Beli 2 setrika baru', 750000],
            ['2026-09-10', 'out', 'Bahan Baku', 'Deterjen & pewangi', 1200000],
            ['2026-09-12', 'out', 'Listrik', 'Tagihan listrik', 900000],
            ['2026-09-30', 'out', 'Gaji', 'Gaji karyawan September', 9000000],
        ] as [$d, $t, $c, $k, $a]) {
            Transaction::create(['date' => $d, 'type' => $t, 'category' => $c, 'description' => $k, 'amount' => $a]);
        }

        // satu pesanan contoh supaya admin langsung punya pesanan masuk
        app(OrderService::class)->create([
            'customer_id' => $aisha->id, 'service' => 'reguler', 'price_per_kg' => 7000,
            'color' => 'Campur Warna', 'perfume' => 'Fresh Clean', 'category' => 'Pakaian Harian',
            'note' => 'Pisahkan pakaian putih', 'pickup_address' => $aisha->address,
            'lat' => -6.3438, 'lng' => 106.737, 'pickup_date' => today(), 'pickup_time' => '10.00–12.00',
        ]);
    }
}