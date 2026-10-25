@extends('layouts.app')

@section('title', 'Kelola Staf & Kasir')

@section('content')
<section class="min-h-[85vh] bg-[#f9f4eb] px-4 py-6 sm:px-6 sm:py-10">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-amber-600">Manajemen Pengguna</p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Kelola Akun Staf & Kasir</h1>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Atur akun kasir dan owner yang memiliki akses ke sistem internal POS.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700">← Kembali ke Dashboard</a>
        </div>

        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px]">
            <!-- Daftar Staf -->
            <div class="space-y-4">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                        <h2 class="text-lg font-black text-slate-900">Daftar Akun Aktif ({{ $staffMembers->count() }})</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($staffMembers as $staff)
                            <div class="p-5 sm:p-6">
                                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-lg font-bold text-slate-900">{{ $staff->name }}</h3>
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $staff->role === 'owner' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $staff->role === 'owner' ? 'Owner / Pemilik' : 'Kasir Warung' }}
                                            </span>
                                            @if ($staff->id === auth()->id())
                                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">Akun Anda</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">{{ $staff->email }} · Terdaftar {{ $staff->created_at->format('d M Y') }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <!-- Tombol Reset Password -->
                                        <button 
                                            type="button" 
                                            onclick="openResetModal({{ $staff->id }}, '{{ addslashes($staff->name) }}')"
                                            class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:border-amber-400 hover:text-amber-700"
                                        >
                                            Reset Password
                                        </button>

                                        @if ($staff->id !== auth()->id())
                                            <form method="POST" action="{{ route('staff.destroy', $staff) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ addslashes($staff->name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Form Tambah Staf Baru -->
            <div>
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black text-slate-900">Tambah Staf Baru</h2>
                    <p class="mt-1 text-xs text-slate-500">Buat akun untuk kasir atau partner baru.</p>

                    <form method="POST" action="{{ route('staff.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Nama Lengkap</label>
                            <input type="text" id="name" name="name" required class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none" placeholder="cth: Siti Rahma">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Email Login</label>
                            <input type="email" id="email" name="email" required class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none" placeholder="kasir2@warung.com">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Peran / Role</label>
                            <select id="role" name="role" required class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none">
                                <option value="cashier">Kasir (Akses POS & Stok)</option>
                                <option value="owner">Owner (Akses Penuh & Laporan)</option>
                            </select>
                            @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Password</label>
                            <input type="password" id="password" name="password" required minlength="8" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none" placeholder="Minimal 8 karakter">
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="w-full rounded-full bg-amber-500 px-5 py-3 text-sm font-bold text-slate-900 shadow-md shadow-amber-500/20 transition hover:bg-amber-400">
                            + Daftarkan Staf
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Reset Password -->
<div id="resetModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <h3 class="text-xl font-black text-slate-900">Reset Password Staf</h3>
        <p id="resetStaffName" class="mt-1 text-sm text-slate-600"></p>

        <form id="resetForm" method="POST" action="" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="resetPasswordInput" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Password Baru</label>
                <input type="password" id="resetPasswordInput" name="password" required minlength="8" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none" placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label for="resetPasswordConfirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Konfirmasi Password Baru</label>
                <input type="password" id="resetPasswordConfirmation" name="password_confirmation" required minlength="8" class="mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none" placeholder="Ulangi password baru">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeResetModal()" class="rounded-full border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-full bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-400">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResetModal(userId, userName) {
        const modal = document.getElementById('resetModal');
        const form = document.getElementById('resetForm');
        const nameEl = document.getElementById('resetStaffName');
        
        form.action = `/manage/staff/${userId}/reset-password`;
        nameEl.textContent = `Mengganti kata sandi untuk akun ${userName}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeResetModal() {
        const modal = document.getElementById('resetModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection

