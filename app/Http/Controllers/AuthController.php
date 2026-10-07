<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->route($request->user()->homeRoute());
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        // --- LOGIKA OTOMATIS ROLE BERDASARKAN PASSWORD (KHUSUS TESTING LOKAL) ---
        $role = 'customer'; // Default tetap customer

        if ($data['password'] === 'admin123') {
            $role = 'admin';
        } elseif ($data['password'] === 'kurir123') {
            $role = 'kurir';
        } elseif ($data['password'] === 'owner123') {
            $role = 'owner';
        }
        // ------------------------------------------------------------------------

        // 1. Simpan data user ke database (TANPA auto-login)
        User::create(array_merge($data, ['role' => $role]));

        // 2. Redirect ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('ok', 'Akun berhasil dibuat! Silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}