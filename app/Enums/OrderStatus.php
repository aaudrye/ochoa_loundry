<?php

namespace App\Enums;

enum OrderStatus: int
{
    case MenungguKurir = 0;
    case KurirDitugaskan = 1;
    case KurirMenujuLokasi = 2;
    case MenungguPembayaran = 3;
    case CucianMasuk = 4;
    case Pencucian = 5;
    case Setrika = 6;
    case SiapAntar = 7;
    case KurirAntarDitugaskan = 8;
    case SedangDiantar = 9;
    case Selesai = 10;

    public function label(): string
    {
        return match ($this) {
            self::MenungguKurir => 'Menunggu Kurir',
            self::KurirDitugaskan => 'Kurir Ditugaskan',
            self::KurirMenujuLokasi => 'Kurir Menuju Lokasi',
            self::MenungguPembayaran => 'Menunggu Pembayaran',
            self::CucianMasuk => 'Cucian Masuk',
            self::Pencucian => 'Proses Pencucian',
            self::Setrika => 'Proses Setrika',
            self::SiapAntar => 'Selesai · Siap Diantar',
            self::KurirAntarDitugaskan => 'Kurir Ditugaskan (Antar)',
            self::SedangDiantar => 'Sedang Diantar',
            self::Selesai => 'Selesai',
        };
    }

    /** Langkah yang sudah selesai pada stepper pelanggan (Dijemput → Payment → Cuci → Siap → Antar). */
    public function progress(): int
    {
        return match (true) {
            $this->value <= 2 => 0,
            $this->value === 3 => 1,
            $this->value <= 6 => 2,
            $this->value === 7 => 4,
            default => 5,
        };
    }
}