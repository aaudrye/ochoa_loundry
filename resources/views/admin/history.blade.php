@extends('layouts.app')
@section('content')
<h1>Histori</h1>
<p class="mu">Rekam jejak lengkap setiap pesanan.</p>
@foreach ($orders as $o)
<details class="card" @if ($loop->first) open @endif>
    <summary style="cursor:pointer;font-weight:800;color:var(--navy)">#{{ $o->code }} — {{ $o->customer->name }} @include('partials.badge', ['order' => $o])</summary>
    <div style="margin-top:14px">@include('partials.timeline', ['order' => $o])</div>
</details>
@endforeach
@endsection