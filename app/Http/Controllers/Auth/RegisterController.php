<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => self::normalizePhone((string) $request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^628\d{8,12}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        // role dan email_verified_at tidak ada di $fillable: role default 'customer'.
        $user = User::create($data);

        // Memicu email verifikasi (User implements MustVerifyEmail).
        event(new Registered($user));

        // Tanpa login otomatis: pelanggan login ulang (keputusan pemilik).
        return redirect()->route('login')->with(
            'status',
            'Akun berhasil dibuat. Kami mengirim tautan verifikasi ke email Anda. Silakan login setelah itu.'
        );
    }

    /** 08xx / +62 8xx / 62 8xx / 8xx  ->  628xx */
    public static function normalizePhone(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        return match (true) {
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }
}
