@extends('layouts.app')
@section('content')
<h1>Dashboard Owner</h1>
<p class="mu">Cabang Pamulang · {{ now()->translatedFormat('F Y') }}</p>
<div class="g g4 k">
    <div class="card"><div class="mu">Pendapatan Bulan Ini</div><div class="num">@rp($income)</div></div>
    <div class="card"><div class="mu">Pengeluaran Bulan Ini</div><div class="num">@rp($expense)</div></div>
    <div class="card"><div class="mu">Laba Bersih</div><div class="num">@rp($income - $expense)</div></div>
    <div class="card"><div class="mu">Pesanan Bulan Ini</div><div class="num">{{ $orders }}</div></div>
</div>
<div class="card"><h3>Pendapatan per Minggu</h3>
    @foreach ($weekly as $label => $val)
        <div class="mu">{{ $label }} · @rp($val)</div>
        <div style="height:9px;border-radius:9px;background:var(--ln);margin:3px 0 8px"><div style="width:{{ round($val / $max * 100) }}%;height:100%;border-radius:9px;background:var(--b)"></div></div>
    @endforeach
</div>
@endsection