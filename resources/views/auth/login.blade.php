@extends('layouts.app')
@section('title', 'Login · OCHOA')
@section('content')
<div class="auth">
    <div class="brand" style="justify-content:center"><div class="logo">O</div>OCHOA</div>
    <p class="mu" style="text-align:center">Laundry antar-jemput, timbang transparan.</p>
    <div class="card">
        <h3>Login</h3>
        @if ($errors->any()) <p style="color:var(--rd)">{{ $errors->first() }}</p> @endif
        <form method="POST" action="{{ route('login') }}">@csrf
            <div class="f"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" required autofocus></div>
            <div class="f"><label>Password</label><input type="password" name="password" required></div>
            <button class="btn w">Login</button>
        </form>
        <p class="mu" style="text-align:center;margin:12px 0 0">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </div>
</div>
@endsection