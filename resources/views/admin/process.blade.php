@extends('layouts.app')
@section('content')

<h1>Proses Pesanan</h1>
<p class="mu">Cucian Masuk → Pencucian → Setrika → Selesai → Hubungi Pelanggan → Tugaskan Kurir</p>

@php
    $next = [4 => 'Proses Pencucian', 5 => 'Proses Setrika', 6 => 'Selesai'];
@endphp

@forelse ($orders as $o)
<div class="card">
    <div class="row">
        <div>
            <b>#{{ $o->code }}</b> · {{ $o->customer->name }}
            <div class="mu">
                {{ ucfirst($o->service) }} · {{ $o->weight_kg + 0 }} Kg · @rp($o->total) <span class="bd g1">Lunas</span>
            </div>
        </div>
        <span class="bd o1">{{ $o->status->label() }}</span>
    </div>

    <div class="st">
        @foreach (['Cucian Masuk', 'Pencucian', 'Setrika', 'Selesai'] as $j => $t)
            <i class="{{ $j <= min($o->status->value - 4, 3) ? 'd' : '' }}">{{ $t }}</i>
        @endforeach
    </div>

    @if (isset($next[$o->status->value]))
        <form method="POST" action="{{ route('admin.advance', $o) }}">@csrf
            <button class="btn">Update Status → {{ $next[$o->status->value] }}</button>
        </form>
    @elseif ($o->status->value === 7)
        <div class="card" style="background:var(--soft);margin:0">
            <b>Laundry #{{ $o->code }} sudah selesai.</b>
            <div class="mu" style="margin:4px 0 10px">
                Hubungi pelanggan untuk menentukan jadwal pengantaran.
                @if ($o->delivery_slot)<br>Jadwal: <b>{{ $o->delivery_slot }}</b>@endif
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                
                @php
                    $custPhone = $o->customer->phone ?? $o->customer->tel ?? '';
                    $cleanCustPhone = preg_replace('/[^0-9]/', '', $custPhone);
                    $waCustNumber = preg_replace('/^0/', '62', $cleanCustPhone);
                    $textCustWA = urlencode("Halo Kak {$o->customer->name}, laundry pesanan #{$o->code} sudah SELESAI. Kira-kira mau diantar jam berapa ya Kak?");
                @endphp

                <a class="btn s o" style="text-decoration:none" href="https://wa.me/{{ $waCustNumber }}?text={{ $textCustWA }}" target="_blank">
                    📞 Hubungi Pelanggan · {{ $o->customer->phone }}
                </a>

                @if ($o->delivery_slot)
                    <a class="btn s" style="text-decoration:none" href="{{ route('admin.assign.form', $o) }}">Tugaskan Kurir</a>
                @else
                    <form method="POST" action="{{ route('admin.slot', $o) }}" class="inl">@csrf
                        <select name="slot" required>
                            <option value="">Pilih jadwal…</option>
                            @foreach (config('ochoa.delivery_slots') as $day => $times) 
                                @foreach ($times as $t)
                                    <option>{{ $day }} {{ $t }}</option>
                                @endforeach 
                            @endforeach
                        </select>
                        <button class="btn s l">Atur Jadwal</button>
                    </form>
                @endif
            </div>
        </div>
    @else
        <div class="card" style="background:var(--soft);margin:0">
            <b>{{ $o->status->value === 8 ? 'Kurir ditugaskan: ' : 'Sedang diantar oleh ' }}{{ $o->deliveryCourier->name ?? '-' }}</b>
            @if ($o->deliveryCourier)
                @php
                    $courierPhone = $o->deliveryCourier->phone ?? $o->deliveryCourier->tel ?? '';
                    $cleanCourierPhone = preg_replace('/[^0-9]/', '', $courierPhone);
                    $waCourierNumber = preg_replace('/^0/', '62', $cleanCourierPhone);
                    $textCourierWA = urlencode("Halo {$o->deliveryCourier->name}, tolong antar pesanan #{$o->code} ke rumah Kak {$o->customer->name} ya.");
                @endphp
                <div style="margin-top:8px">
                    <a class="btn s o" style="text-decoration:none" href="https://wa.me/{{ $waCourierNumber }}?text={{ $textCourierWA }}" target="_blank">
                        📞 Hubungi Kurir
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
@empty
<div class="card mu">Belum ada pesanan yang diproses. Pesanan masuk di sini setelah dijemput, ditimbang, dan dibayar.</div>
@endforelse

@endsection