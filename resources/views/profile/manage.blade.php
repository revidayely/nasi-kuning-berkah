@extends('layouts.app')

@section('title', 'Profil Akun')

@section('content')
<section class="min-h-[80vh] bg-[#f9f4eb] px-4 py-7 sm:px-6 sm:py-10">
    <div class="mx-auto max-w-3xl">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-700">Keamanan akun</p>
        <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Profil Saya</h1>
        <p class="mt-2 text-sm text-slate-600">Kelola identitas akun dan kata sandi login.</p>

        @if (session('status'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">Perubahan berhasil disimpan.</div>
        @endif

        <div class="mt-7 space-y-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-black text-slate-900">Informasi akun</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div><label for="profile-name" class="mb-1.5 block text-sm font-semibold text-slate-700">Nama</label><input id="profile-name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base outline-none focus:border-amber-400 focus:bg-white">@error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label for="profile-email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label><input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base outline-none focus:border-amber-400 focus:bg-white">@error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <button class="min-h-11 rounded-full bg-amber-500 px-5 py-2 text-sm font-bold text-slate-900 hover:bg-amber-400">Simpan profil</button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-black text-slate-900">Ganti kata sandi</h2>
                <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div><label for="current-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata sandi saat ini</label><input id="current-password" name="current_password" type="password" required autocomplete="current-password" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base outline-none focus:border-amber-400 focus:bg-white">@error('current_password', 'updatePassword')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label for="new-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata sandi baru</label><input id="new-password" name="password" type="password" required autocomplete="new-password" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base outline-none focus:border-amber-400 focus:bg-white">@error('password', 'updatePassword')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <div><label for="new-password-confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">Ulangi kata sandi baru</label><input id="new-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base outline-none focus:border-amber-400 focus:bg-white"></div>
                    <button class="min-h-11 rounded-full bg-slate-900 px-5 py-2 text-sm font-bold text-white hover:bg-slate-700">Perbarui kata sandi</button>
                </form>
            </section>

            <section class="rounded-3xl border border-red-200 bg-white p-5 shadow-sm sm:p-7">
                <h2 class="text-lg font-black text-red-800">Hapus akun</h2>
                <p class="mt-2 text-sm text-slate-600">Akun yang dihapus tidak dapat digunakan untuk login kembali.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    @method('DELETE')
                    <div class="flex-1"><label for="delete-password" class="mb-1.5 block text-sm font-semibold text-slate-700">Konfirmasi dengan kata sandi</label><input id="delete-password" name="password" type="password" required autocomplete="current-password" class="min-h-12 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-base outline-none focus:border-red-400 focus:bg-white">@error('password', 'userDeletion')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror</div>
                    <button onclick="return confirm('Yakin ingin menghapus akun ini?')" class="min-h-12 rounded-full bg-red-700 px-5 py-3 text-sm font-bold text-white hover:bg-red-600">Hapus akun saya</button>
                </form>
            </section>
        </div>
    </div>
</section>
@endsection
