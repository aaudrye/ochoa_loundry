@extends('layouts.app')
@section('content')
<h1>Riwayat Antar-Jemput</h1>
<p class="mu">Ketuk untuk melihat detail perjalanan.</p>
@forelse ($items as $i)
    @php($o = $i['order'])
    <details class="card">
        <summary class="row" style="cursor:pointer">
            <span><b>{{ $i['type'] }} #{{ $o->code }}</b><br><span class="mu">{{ $o->customer->name }} · {{ $i['type'] === 'Penjemputan' ? 'Ditimbang ' . ($o->weight_kg + 0) . ' Kg' : $o->delivery_slot }}</span></span>
            <span class="bd g1">Selesai ›</span>
        </summary>
        <div style="margin-top:12px">
            <div class="sm"><span>Alamat</span><b>{{ $o->pickup_address }}</b></div>
            <div class="sm"><span>No. HP</span><b>{{ $o->customer->phone }}</b></div>
            @include('partials.timeline', ['order' => $o])
        </div>
    </details>
@empty
    <div class="card mu">Belum ada riwayat.</div>
@endforelse
@endsection