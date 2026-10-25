<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('make:staff-user {email} {--name=} {--role=cashier}', function () {
    $email = strtolower(trim((string) $this->argument('email')));
    $role = (string) $this->option('role');

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! in_array($role, ['owner', 'cashier'], true)) {
        $this->error('Email harus valid dan role harus owner atau cashier.');

        return 1;
    }

    $password = $this->secret('Password akun (minimal 12 karakter)');
    if (strlen((string) $password) < 12) {
        $this->error('Password minimal 12 karakter.');

        return 1;
    }

    $user = User::query()->updateOrCreate(
        ['email' => $email],
        [
            'name' => $this->option('name') ?: Str::before($email, '@'),
            'role' => $role,
            'password' => Hash::make($password),
        ]
    );

    $this->info("Akun {$user->email} berhasil disimpan sebagai {$user->role}.");

    return 0;
})->purpose('Buat atau perbarui akun owner/kasir secara aman');

Schedule::call(function (): void {
    Transaction::query()
        ->where('status', 'pending')
        ->where('expires_at', '<=', now())
        ->update(['status' => 'cancelled']);
})->everyFiveMinutes()->name('expire-stale-orders')->withoutOverlapping();
