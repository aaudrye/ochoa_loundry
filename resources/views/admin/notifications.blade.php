@extends('layouts.app')
@section('content')
<h1>Notifikasi</h1>
<p class="mu">Aktivitas terbaru semua pesanan.</p>
<div class="card">
    @forelse ($logs as $l)
        <div class="nt">{{ $l->message }} <span class="mu">· #{{ $l->order->code }} · {{ $l->order->customer->name }} · {{ $l->created_at->format('d/m H.i') }}</span></div>
    @empty
        <span class="mu">Belum ada notifikasi.</span>
    @endforelse
</div>
@endsection