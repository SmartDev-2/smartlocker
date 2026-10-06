<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Loker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>



<body class="bg-slate-100 text-slate-800 min-h-screen">

    {{-- Header --}}
    <header class="bg-slate-900 text-white">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🔐</span>
                <h1 class="text-lg font-semibold">Smart Loker</h1>
            </div>
            <span class="text-sm text-slate-300">Loker #{{ $locker['nomor'] }}</span>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6 space-y-6">

        {{-- Notifikasi flash --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- 1. STATUS LOKER --}}
        <section class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Status Loker</h2>

            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="flex items-center gap-4">
                    <div id="status-icon" class="w-16 h-16 rounded-full flex items-center justify-center text-3xl
                        {{ $locker['terkunci'] ? 'bg-green-100' : 'bg-red-100' }}">
                        {{ $locker['terkunci'] ? '🔒' : '🔓' }}
                    </div>
                    <div>
                        <p id="status-text" class="text-2xl font-bold {{ $locker['terkunci'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $locker['terkunci'] ? 'Terkunci' : 'Terbuka' }}
                        </p>
                        <p class="text-sm text-slate-500">Loker #<span id="loker-nomor">{{ $locker['nomor'] }}</span></p>
                    </div>
                </div>

                <div class="sm:ml-auto bg-slate-50 rounded-xl px-5 py-3 text-sm">
                    <p class="text-slate-500">Terakhir dibuka</p>
                    <p id="last-opened" class="font-semibold">{{ $locker['terakhir_buka'] }}</p>
                    <p class="text-slate-500">Kartu: <span id="last-card" class="font-mono">{{ $locker['kartu_terakhir'] }}</span></p>
                </div>
            </div>
        </section>

        {{-- 2. RIWAYAT AKSES --}}
        <section class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Riwayat Akses</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="text-slate-500 border-b">
                            <th class="py-2 pr-4 font-medium">Waktu</th>
                            <th class="py-2 pr-4 font-medium">Kartu RFID</th>
                            <th class="py-2 pr-4 font-medium">Hasil</th>
                            <th class="py-2 font-medium">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr class="border-b last:border-0">
                                <td class="py-3 pr-4 whitespace-nowrap">{{ $log['waktu'] }}</td>
                                <td class="py-3 pr-4 font-mono">{{ $log['kartu'] }}</td>
                                <td class="py-3 pr-4">
                                    @if ($log['hasil'] === 'berhasil')
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Berhasil</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Ditolak</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if ($log['email'] === 'terkirim')
                                        <span class="text-green-600">✔ Terkirim</span>
                                    @else
                                        <span class="text-red-600">✖ Gagal</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- 3. PENGATURAN --}}
        <section class="bg-white rounded-2xl shadow-sm p-6 space-y-8">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">Pengaturan</h2>

            {{-- Email notifikasi --}}
            <div>
                <h3 class="font-semibold mb-2">Email notifikasi</h3>
                <p class="text-sm text-slate-500 mb-3">Laporan dikirim ke email ini setiap loker dibuka dengan RFID.</p>
                <form method="POST" action="{{ url('/pengaturan/email') }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="email" name="email" value="{{ old('email', $notifEmail) }}" required
                        class="flex-1 border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <button type="submit"
                        class="bg-slate-900 hover:bg-slate-700 text-white px-5 py-2 rounded-lg text-sm font-medium">
                        Simpan
                    </button>
                </form>
            </div>

            {{-- Kartu RFID --}}
            <div>
                <h3 class="font-semibold mb-2">Kartu RFID terdaftar</h3>

                <ul class="divide-y border rounded-lg mb-4">
                    @forelse ($cards as $card)
                        <li class="flex items-center justify-between px-4 py-3">
                            <div>
                                <p class="font-medium">{{ $card['nama'] }}</p>
                                <p class="text-sm text-slate-500 font-mono">{{ $card['uid'] }}</p>
                            </div>
                            <form method="POST" action="{{ url('/kartu/' . $card['id']) }}"
                                  onsubmit="return confirm('Hapus kartu ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                            </form>
                        </li>
                    @empty
                        <li class="px-4 py-4 text-sm text-slate-400 text-center">Belum ada kartu.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ url('/kartu') }}" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="text" name="nama" placeholder="Nama kartu" required
                        class="flex-1 border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <input type="text" name="uid" placeholder="UID kartu (mis. A3F2...)" required
                        class="flex-1 border border-slate-300 rounded-lg px-3 py-2 font-mono focus:outline-none focus:ring-2 focus:ring-slate-400">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-500 text-white px-5 py-2 rounded-lg text-sm font-medium">
                        + Tambah
                    </button>
                </form>
            </div>
        </section>

    </main>

    <footer class="text-center text-xs text-slate-400 py-6">
        Smart Loker &copy; {{ date('Y') }}
    </footer>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-database.js";

        const firebaseConfig = {
            apiKey: "AIzaSyDtkGRXR8L_rmnDSkYbGvL_7G0QItVax28",
            authDomain: "smartlocker-96fe1.firebaseapp.com",
            projectId: "smartlocker-96fe1",
            storageBucket: "smartlocker-96fe1.firebasestorage.app",
            messagingSenderId: "1055053296910",
            appId: "1:1055053296910:web:70812f1c7e23dc22db935f",
            databaseURL: "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app"
        };

        const app = initializeApp(firebaseConfig);
        const database = getDatabase(app);

        // Listen to locker status
        const lockerRef = ref(database, 'locker_status');
        
        onValue(lockerRef, (snapshot) => {
            const data = snapshot.val();
            if (data) {
                const icon = document.getElementById('status-icon');
                const text = document.getElementById('status-text');
                const lastOpened = document.getElementById('last-opened');
                const lastCard = document.getElementById('last-card');

                if (data.terkunci) {
                    icon.innerHTML = '🔒';
                    icon.className = 'w-16 h-16 rounded-full flex items-center justify-center text-3xl bg-green-100';
                    text.innerText = 'Terkunci';
                    text.className = 'text-2xl font-bold text-green-600';
                } else {
                    icon.innerHTML = '🔓';
                    icon.className = 'w-16 h-16 rounded-full flex items-center justify-center text-3xl bg-red-100';
                    text.innerText = 'Terbuka';
                    text.className = 'text-2xl font-bold text-red-600';
                }

                if (data.terakhir_buka) {
                    lastOpened.innerText = data.terakhir_buka;
                }
                
                if (data.kartu_terakhir) {
                    lastCard.innerText = data.kartu_terakhir;
                }
            }
        });
    </script>
</body>
</html>
