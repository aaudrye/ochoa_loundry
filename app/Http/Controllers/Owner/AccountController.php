<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function edit() { return view('owner.account'); }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->user()->id)],
            'phone' => 'nullable|string|max:20',
        ]);
        $request->user()->update($data);

        return back()->with('ok', 'Informasi akun disimpan.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:6|confirmed',
        ]);
        $request->user()->update(['password' => $request->password]);

        return back()->with('ok', 'Password berhasil diubah.');
    }
}