@extends('layouts.app')
@section('content')
<h1>Pengaturan Akun</h1>
<div class="g g2">
    <div class="card"><h3>Informasi Akun</h3>
        <form method="POST" action="{{ route('owner.account.update') }}">@csrf @method('PUT')
            <div class="f"><label>Nama Akun</label><input name="name" value="{{ old('name', auth()->user()->name) }}" required></div>
            <div class="f"><label>Email</label><input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required></div>
            <div class="f"><label>Nomor HP</label><input name="phone" value="{{ old('phone', auth()->user()->phone) }}"></div>
            <button class="btn">Simpan</button>
        </form></div>
    <div class="card"><h3>Ubah Password</h3>
        <form method="POST" action="{{ route('owner.account.password') }}">@csrf @method('PUT')
            <div class="f"><label>Password Lama</label><input type="password" name="current_password" required></div>
            <div class="f"><label>Password Baru</label><input type="password" name="password" required></div>
            <div class="f"><label>Konfirmasi Password Baru</label><input type="password" name="password_confirmation" required></div>
            <button class="btn">Ubah Password</button>
        </form></div>
</div>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn o">Logout</button></form>
@endsection