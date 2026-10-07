<?php

namespace App\Services;

use App\Enums\OrderStatus as S;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Semua perpindahan status pesanan lewat sini supaya alur
 * (jemput → timbang → bayar → cuci → setrika → antar) konsisten dan tercatat di log.
 */
class OrderService
{
    public function log(Order $o, string $msg, ?User $by = null, ?S $at = null): void
    {
        $o->logs()->create([
            'status' => ($at ?? $o->status)->value,
            'message' => $msg,
            'user_id' => $by?->id,
        ]);
    }

    public function transition(Order $o, S $to, string $msg, ?User $by = null): void
    {
        $o->update(['status' => $to]);
        $this->log($o, $msg, $by, $to);
    }

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create($data);
            $order->update(['code' => 'OCH' . (1000 + $order->id)]);
            $this->log($order, 'Pesanan dibuat, menunggu kurir', $order->customer, S::MenungguKurir);

            return $order;
        });
    }

    /** Admin menugaskan kurir (jemput jika status 0, antar jika status 7). */
    public function assign(Order $o, User $courier, User $by): void
    {
        abort_unless($courier->role === 'kurir', 422, 'User bukan kurir.');
        abort_if($courier->isBusy(), 422, 'Kurir sedang bertugas.');

        DB::transaction(function () use ($o, $courier, $by) {
            if ($o->status === S::MenungguKurir) {
                $o->update(['pickup_courier_id' => $courier->id, 'arrived' => false]);
                $this->transition($o, S::KurirDitugaskan, "{$courier->name} ditugaskan untuk penjemputan", $by);
            } elseif ($o->status === S::SiapAntar) {
                abort_unless($o->delivery_slot, 422, 'Jadwal pengantaran belum ditentukan.');
                $o->update(['delivery_courier_id' => $courier->id, 'arrived' => false]);
                $this->transition($o, S::KurirAntarDitugaskan, "{$courier->name} ditugaskan untuk pengantaran", $by);
            } else {
                abort(422, 'Status pesanan tidak bisa ditugaskan kurir.');
            }
        });
    }

    public function acceptPickup(Order $o, User $courier): void
    {
        abort_unless($o->status === S::KurirDitugaskan && $o->pickup_courier_id === $courier->id, 422);
        DB::transaction(function () use ($o, $courier) {
            $this->log($o, 'Tugas diterima', $courier);
            $this->transition($o, S::KurirMenujuLokasi, 'OTW menuju lokasi pelanggan', $courier);
        });
    }

    public function arrive(Order $o, User $courier): void
    {
        $mine = ($o->status === S::KurirMenujuLokasi && $o->pickup_courier_id === $courier->id)
             || ($o->status === S::SedangDiantar && $o->delivery_courier_id === $courier->id);
        abort_unless($mine, 422);

        $o->update(['arrived' => true]);
        $this->log($o, 'Kurir sampai di lokasi', $courier);
    }

    public function weigh(Order $o, User $courier, float $kg): void
    {
        abort_unless($o->status === S::KurirMenujuLokasi && $o->arrived && $o->pickup_courier_id === $courier->id, 422);

        DB::transaction(function () use ($o, $courier, $kg) {
            $o->update(['weight_kg' => $kg]);
            $this->log($o, 'Laundry dijemput', $courier, S::MenungguPembayaran);
            $this->log($o, "Ditimbang {$kg} kg", $courier, S::MenungguPembayaran);
            $this->transition($o, S::MenungguPembayaran, 'Tagihan dikirim ke pelanggan: Rp' . number_format($o->total, 0, ',', '.'), $courier);
        });
    }

    /** Pembayaran diterima → pemasukan otomatis masuk ke keuangan. */
    public function pay(Order $o, User $customer): void
    {
        abort_unless($o->status === S::MenungguPembayaran && $o->customer_id === $customer->id, 422);

        DB::transaction(function () use ($o, $customer) {
            $o->update(['paid_at' => now()]);
            Transaction::create([
                'date' => today(), 'type' => 'in', 'category' => 'Laundry',
                'description' => "Pendapatan laundry #{$o->code}", 'amount' => $o->total, 'order_id' => $o->id,
            ]);
            $this->transition($o, S::CucianMasuk, 'Pembayaran diterima Rp' . number_format($o->total, 0, ',', '.'), $customer);
        });
    }

    public function setDeliverySlot(Order $o, string $slot, User $by): void
    {
        abort_unless($o->status === S::SiapAntar, 422);
        $allowed = collect(config('ochoa.delivery_slots'))->flatMap(fn ($times, $day) => collect($times)->map(fn ($t) => "$day $t"));
        abort_unless($allowed->contains($slot), 422, 'Jadwal tidak valid.');

        $o->update(['delivery_slot' => $slot]);
        $this->log($o, "Jadwal pengantaran ditetapkan: {$slot}", $by);
    }

    /** Admin: cucian masuk → pencucian → setrika → selesai. */
    public function advance(Order $o, User $by): void
    {
        [$to, $msg] = match ($o->status) {
            S::CucianMasuk => [S::Pencucian, 'Proses pencucian dimulai'],
            S::Pencucian => [S::Setrika, 'Proses setrika dimulai'],
            S::Setrika => [S::SiapAntar, 'Laundry selesai'],
            default => abort(422, 'Status tidak bisa dimajukan.'),
        };
        $this->transition($o, $to, $msg, $by);
    }

    public function acceptDelivery(Order $o, User $courier): void
    {
        abort_unless($o->status === S::KurirAntarDitugaskan && $o->delivery_courier_id === $courier->id, 422);
        DB::transaction(function () use ($o, $courier) {
            $this->log($o, 'Tugas pengantaran diterima', $courier);
            $this->transition($o, S::SedangDiantar, 'OTW mengantar laundry', $courier);
        });
    }

    public function deliver(Order $o, User $courier): void
    {
        abort_unless($o->status === S::SedangDiantar && $o->arrived && $o->delivery_courier_id === $courier->id, 422);
        $this->transition($o, S::Selesai, 'Laundry diserahkan & diterima pelanggan', $courier);
    }
}