<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->error('ADMIN_EMAIL dan ADMIN_PASSWORD wajib diisi di .env sebelum seeding.');

            return;
        }

        // role dan email_verified_at tidak ada di $fillable, jadi diisi eksplisit.
        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = env('ADMIN_NAME', 'Admin Kazoku');
        $admin->password = $password; // di-hash oleh cast 'hashed'
        $admin->role = UserRole::Admin;
        $admin->email_verified_at = now();
        $admin->save();
    }
}
