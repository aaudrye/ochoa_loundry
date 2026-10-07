<html><head><meta charset="utf-8"></head><body>
<h3>OCHOA Laundry - Laporan Keuangan</h3>
<p>Periode {{ \Carbon\Carbon::parse($f['from'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($f['to'])->format('d/m/Y') }} · {{ $rows->count() }} transaksi</p>
<table border="1">
    <tr><th>No</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Nominal (Rp)</th></tr>
    @foreach ($rows as $i => $r)
        <tr><td>{{ $i + 1 }}</td><td>{{ $r->date->format('d/m/Y') }}</td><td>{{ $r->type === 'in' ? 'Pemasukan' : 'Pengeluaran' }}</td><td>{{ $r->category }}</td><td>{{ $r->description }}</td><td>{{ $r->amount }}</td></tr>
    @endforeach
    @foreach (['Total Pemasukan' => $sum['in'], 'Total Pengeluaran' => $sum['out'], 'Total Gaji' => $sum['gaji'], 'Laba Bersih' => $sum['laba']] as $l => $v)
        <tr><td colspan="5"><b>{{ $l }}</b></td><td><b>{{ $v }}</b></td></tr>
    @endforeach
</table>
</body></html>