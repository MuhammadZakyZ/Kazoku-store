<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        // Respons selalu sama, terdaftar atau tidak, agar email tidak bisa ditebak.
        return back()->with(
            'status',
            'Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi sudah dikirim.'
        );
    }
}
