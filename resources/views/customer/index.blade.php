@extends('layouts.app')
@section('content')
<h1>Pesanan Saya</h1>
<p class="mu">Jemput → Payment → Cuci → Siap → Antar</p>
@forelse ($orders as $o) @include('customer._card', ['order' => $o]) @empty <div class="card mu">Belum ada pesanan.</div> @endforelse
@endsection