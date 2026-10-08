@extends('layouts.app')

@section('title', 'Pengaturan - Smart Loker')

@section('content')

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola daftar penerima notifikasi email dan kartu RFID terdaftar.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        {{-- 1. EMAIL NOTIFIKASI --}}
        <section class="bg-white rounded-3xl shadow-sm border border-slate-200/70 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 flex items-start gap-4">
                <div class="w-11 h-11 shrink-0 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800">Email Notifikasi Akses</h3>
                        <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">{{ count($emails) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Laporan dikirim otomatis ke email ini setiap kali loker dibuka.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100">
                    @forelse ($emails as $email)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 shrink-0 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">@</div>
                                <p class="font-medium text-sm text-slate-800 truncate">{{ $email['alamat'] }}</p>
                            </div>
                            <form method="POST" action="{{ route('email.destroy', $email['id']) }}" onsubmit="return confirm('Hapus email {{ $email['alamat'] }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-all sm:opacity-0 sm:group-hover:opacity-100">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-sm text-slate-400 text-center">Belum ada email yang terdaftar.</div>
                    @endforelse
                </div>

                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/70">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Tambah Email Penerima</h4>
                    <form method="POST" action="{{ route('email.store') }}" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="email" name="email" placeholder="contoh: admin@email.com" required
                            class="flex-1 border border-slate-300 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm shadow-indigo-200">
                            Tambah
                        </button>
                    </form>
                </div>
            </div>
        </section>

        {{-- 2. KARTU RFID --}}
        <section class="bg-white rounded-3xl shadow-sm border border-slate-200/70 overflow-hidden">
            <div class="px-6 sm:px-8 py-6 border-b border-slate-100 flex items-start gap-4">
                <div class="w-11 h-11 shrink-0 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800">Kartu RFID Terdaftar</h3>
                        <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">{{ count($cards) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Hanya kartu yang terdaftar yang dapat membuka akses loker.</p>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100">
                    @forelse ($cards as $card)
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-[10px]">RFID</div>
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-slate-800 truncate">{{ $card['nama'] }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $card['uid'] }}</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('kartu.destroy', $card['id']) }}" onsubmit="return confirm('Hapus kartu {{ $card['nama'] }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition-all sm:opacity-0 sm:group-hover:opacity-100">Hapus</button>
                            </form>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-sm text-slate-400 text-center">Belum ada kartu RFID yang terdaftar.</div>
                    @endforelse
                </div>

                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/70">
                    <h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">Tambah Kartu Baru</h4>
                    <form method="POST" action="{{ route('kartu.store') }}" class="flex flex-col gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Pemilik / Fungsi</label>
                            <input type="text" name="nama" placeholder="Contoh: Budi, Cleaning Service" required
                                class="w-full border border-slate-300 bg-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition-all">
                        </div>

                        <input type="hidden" name="uid" id="uid_input" required>

                        <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-300 rounded-2xl bg-white transition-all" id="scan_container">
                            <div class="relative flex items-center justify-center mb-3 w-16 h-16">
                                <div class="absolute w-full h-full rounded-full bg-emerald-400 opacity-20 animate-ping"></div>
                                <div class="absolute w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center border border-emerald-200">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </div>
                            </div>
                            <p class="text-sm font-bold text-slate-700 mb-1" id="scan_text">Tempelkan kartu anda</p>
                            <p class="text-xs text-slate-500 text-center" id="scan_uid_display">Sistem siap mendeteksi kartu...</p>
                        </div>

                        <button type="submit" id="btn_tambah" disabled
                            class="bg-emerald-600 disabled:bg-slate-300 disabled:cursor-not-allowed hover:bg-emerald-700 text-white w-full py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm shadow-emerald-200">
                            Tambah
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script type="module">
        import { initializeApp, getApps, getApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-database.js";

        const firebaseConfig = {
            databaseURL: "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app"
        };

        const app = getApps().length === 0 ? initializeApp(firebaseConfig) : getApp();
        const database = getDatabase(app);

        const lockerRef = ref(database, 'locker_status');

        let initialLoad = true;

        onValue(lockerRef, (snapshot) => {
            const data = snapshot.val();
            if (!data) return;

            if (initialLoad) {
                initialLoad = false;
                return; // Abaikan data saat pertama load agar tidak langsung terisi dengan kartu lama
            }

            // Tangkap kartu saat sistem Arduino mengirim data scan baru (terkunci: false)
            if (data.terkunci === false && data.kartu_terakhir) {
                const uid = data.kartu_terakhir;
                const container = document.getElementById('scan_container');
                const text = document.getElementById('scan_text');
                const display = document.getElementById('scan_uid_display');
                const input = document.getElementById('uid_input');
                const btn = document.getElementById('btn_tambah');

                container.classList.remove('border-slate-300');
                container.classList.add('border-emerald-500', 'bg-emerald-50/50');

                text.innerText = 'Kartu Terdeteksi!';
                text.classList.remove('text-slate-700');
                text.classList.add('text-emerald-700');

                display.innerText = 'UID: ' + uid;
                display.classList.remove('text-slate-500');
                display.classList.add('text-emerald-600', 'font-mono', 'font-bold');

                document.querySelector('#scan_container .animate-ping').style.display = 'none';

                input.value = uid;
                btn.removeAttribute('disabled');
            }
        });
    </script>

@endsection
