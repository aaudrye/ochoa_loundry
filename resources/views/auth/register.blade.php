@extends('layouts.app')
@section('title', 'Daftar · OCHOA')
@section('content')
<div class="auth">
    <div class="brand" style="justify-content:center"><div class="logo">O</div>OCHOA</div>
    <div class="card">
        <h3>Daftar Akun Pelanggan</h3>
        @if ($errors->any()) <p style="color:var(--rd)">{{ $errors->first() }}</p> @endif
        <form method="POST" action="{{ route('register') }}">@csrf
            <div class="f"><label>Nama Lengkap</label><input name="name" value="{{ old('name') }}" required></div>
            <div class="f"><label>Nomor Telepon</label><input name="phone" value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx" required></div>
            <div class="f"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" required></div>
            <div class="f"><label>Password</label><input type="password" name="password" required></div>
            <button class="btn w">Daftar & Login</button>
        </form>
        <p class="mu" style="text-align:center;margin:12px 0 0">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
    </div>
</div>
@endsection