@extends('layouts.app')
@section('content')
<h1>Halo, {{ auth()->user()->name }}</h1>
<span class="bd {{ $jobs->count() ? 'o1' : 'g1' }}">{{ $jobs->count() ? '🟡 Sedang Bertugas' : '🟢 Free' }}</span>
<div class="g g2" style="margin-top:12px">
    <div class="card"><div class="mu">Tugas Aktif</div><div class="num">{{ $jobs->count() }}</div></div>
    <div class="card"><div class="mu">Selesai Hari Ini</div><div class="num">{{ $done }}</div></div>
</div>
<h3>Tugas Saya</h3>
@forelse ($jobs as $o)
<div class="card">
    <div class="row"><b>{{ $o->status->value <= 2 ? '🧺 Jemput' : '📦 Antar' }} #{{ $o->code }}</b><span class="bd o1">{{ $o->status->label() }}</span></div>
    <div>{{ $o->customer->name }}</div>
    <div class="mu">📞 {{ $o->customer->phone }}<br>📍 {{ $o->pickup_address }}</div>
    <a class="btn s" style="margin-top:8px;text-decoration:none;display:inline-block" href="{{ route('courier.show', $o) }}">Rincian Pesanan</a>
</div>
@empty
<div class="card mu">Belum ada tugas. Tunggu penugasan dari admin.</div>
@endforelse
@endsection