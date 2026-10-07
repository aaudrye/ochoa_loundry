@extends('layouts.app')
@section('content')
<a class="btn s l" style="text-decoration:none" href="{{ url()->previous() }}">← Kembali</a>
<h1 style="margin-top:10px">Pilih Kurir · #{{ $order->code }}</h1>
<p class="mu">Hanya kurir yang <b>Free</b> bisa ditugaskan ({{ $order->status->value === 7 ? 'pengantaran' : 'penjemputan' }}).</p>
<div class="card">
    @foreach ($couriers as $c)
        @php($busy = $c->isBusy())
        <form method="POST" action="{{ route('admin.assign', $order) }}" class="row nt">@csrf
            <input type="hidden" name="courier_id" value="{{ $c->id }}">
            <div><b>{{ $c->name }}</b><br><span class="bd {{ $busy ? 'o1' : 'g1' }}">{{ $busy ? '🔴 Sedang bertugas' : '🟢 Free' }}</span></div>
            <button class="btn s" @disabled($busy)>Tugaskan</button>
        </form>
    @endforeach
</div>
@endsection