@extends('layouts.app')
@section('content')
<h1>Keuangan</h1>
<div class="tabs">
    @foreach (['in' => 'Pemasukan', 'out' => 'Pengeluaran', 'report' => 'Laporan'] as $k => $l)
        <button type="button" class="{{ $tab === $k ? 'on' : '' }}" onclick="location.href='{{ route('owner.finance', ['tab' => $k]) }}'">{{ $l }}</button>
    @endforeach
</div>

@if ($tab !== 'report')
    @php($out = $tab === 'out')
    <div class="card">
        <h3>{{ $out ? 'Catat Pengeluaran' : 'Input Pemasukan' }}</h3>
        <form method="POST" action="{{ route('owner.finance.store') }}">@csrf
            <input type="hidden" name="type" value="{{ $tab }}">
            <div class="g g2">
                <div class="f"><label>Tanggal</label><input type="date" name="date" value="{{ old('date', today()->toDateString()) }}" required></div>
                <div class="f"><label>Kategori</label>
                    <select name="category">@foreach (config($out ? 'ochoa.expense_categories' : 'ochoa.income_categories') as $c)<option>{{ $c }}</option>@endforeach</select></div>
            </div>
            <div class="g g2">
                <div class="f"><label>Nominal</label><input name="amount" inputmode="numeric" placeholder="Rp0" required
                    oninput="var v=this.value.replace(/\D/g,'');this.value=v?'Rp'+(+v).toLocaleString('id-ID'):''"></div>
                <div class="f"><label>Keterangan</label><input name="description" required placeholder="{{ $out ? 'Contoh: Pembelian 2 setrika baru' : 'Contoh: Pendapatan laundry harian' }}"></div>
            </div>
            <button class="btn">Simpan {{ $out ? 'Pengeluaran' : 'Pemasukan' }}</button>
            <span class="mu" style="margin-left:10px">Data otomatis masuk ke Laporan</span>
        </form>
    </div>

    <div class="card tw"><h3>Riwayat {{ $out ? 'Pengeluaran' : 'Pemasukan' }} Terbaru</h3>
        <table><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th></tr>
        @foreach ($recent as $r)<tr><td>{{ $r->date->format('d/m/Y') }}</td><td>{{ $r->category }}</td><td>{{ $r->description }}</td><td>@rp($r->amount)</td></tr>@endforeach</table>
    </div>

    @if ($out)
    <div class="card tw"><h3>Pengeluaran Gaji Karyawan</h3>
        <table><tr><th>Nama</th><th>Jabatan</th><th>Gaji</th><th>Lembur</th><th>Total</th></tr>
        @foreach ($employees as $e)<tr><td>{{ $e->name }}</td><td>{{ $e->position }}</td><td>@rp($e->salary)</td><td>@rp($e->overtime)</td><td><b>@rp($e->total)</b></td></tr>@endforeach
        <tr><td colspan="4"><b>Total Gaji</b></td><td><b>@rp($employees->sum->total)</b></td></tr></table>
    </div>
    @endif
@else
    <form method="GET" action="{{ route('owner.finance') }}" class="card">
        <input type="hidden" name="tab" value="report"><input type="hidden" name="show" value="1">
        <div class="g g3">
            <div class="f"><label>Dari tanggal</label><input type="date" name="from" value="{{ $f['from'] }}"></div>
            <div class="f"><label>Sampai tanggal</label><input type="date" name="to" value="{{ $f['to'] }}"></div>
            <div class="f"><label>Jenis transaksi</label>
                <select name="jenis">@foreach (['all' => 'Semua', 'in' => 'Pemasukan', 'out' => 'Pengeluaran', 'gaji' => 'Gaji'] as $k => $l)<option value="{{ $k }}" @selected($f['jenis'] === $k)>{{ $l }}</option>@endforeach</select></div>
        </div>
        <div class="g g3">
            <div class="f"><label>Kategori</label>
                <select name="category"><option value="">Semua</option>@foreach (array_unique([...config('ochoa.income_categories'), ...config('ochoa.expense_categories')]) as $c)<option @selected($f['category'] === $c)>{{ $c }}</option>@endforeach</select></div>
            <div class="f"><label>Cari</label><input name="q" value="{{ $f['q'] }}" placeholder="Cari keterangan / kategori"></div>
            <div class="f"><label>&nbsp;</label><button class="btn w">Tampilkan Laporan</button></div>
        </div>
    </form>

    @if ($rows === null)
        <div class="card mu">Atur filter lalu klik Tampilkan Laporan untuk melihat preview sebelum export.</div>
    @else
        <div class="g g4 k">
            <div class="card"><div class="mu">Total Pemasukan</div><div class="num">@rp($sum['in'])</div></div>
            <div class="card"><div class="mu">Total Pengeluaran</div><div class="num">@rp($sum['out'])</div></div>
            <div class="card"><div class="mu">Total Gaji</div><div class="num">@rp($sum['gaji'])</div></div>
            <div class="card"><div class="mu">Laba Bersih</div><div class="num">@rp($sum['laba'])</div></div>
        </div>
        <div class="card tw">
            <div class="row"><h3>Detail Transaksi · Preview</h3>
                <a class="btn s" style="text-decoration:none" href="{{ route('owner.finance.export', request()->only('from', 'to', 'jenis', 'category', 'q')) }}">Export Excel</a></div>
            <table><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th></tr>
            @forelse ($rows as $r)
                <tr><td>{{ $r->date->format('d/m/Y') }}</td><td>{{ $r->type === 'in' ? 'Pemasukan' : 'Pengeluaran' }}</td><td>{{ $r->category }}</td><td>{{ $r->description }}</td><td>@rp($r->amount)</td></tr>
            @empty
                <tr><td colspan="5" class="mu">Tidak ada data</td></tr>
            @endforelse</table>
        </div>
    @endif
@endif
@endsection