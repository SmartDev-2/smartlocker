<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Loker')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">

    {{-- Header & Navigasi --}}
    <header class="bg-slate-900 text-white sticky top-0 z-10 shadow-md">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🔐</span>
                    <h1 class="text-lg font-bold">Smart Loker</h1>
                </div>

                {{-- Menu Navigasi --}}
                <nav class="flex gap-1 bg-slate-800 p-1 rounded-xl text-xs sm:text-sm">
                    <a href="{{ route('dashboard') }}" 
                       class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('pengaturan') }}" 
                       class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request()->routeIs('pengaturan') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' }}">
                        Pengaturan
                    </a>
                </nav>
            </div>

            <span class="text-xs text-slate-400 font-mono hidden sm:inline-block">
                Loker #{{ $locker['nomor'] ?? '12' }}
            </span>
        </div>
    </header>

    {{-- Container Utama --}}
    <main class="max-w-5xl mx-auto px-4 py-8 space-y-8 flex-1 w-full">

        {{-- Notifikasi Flash --}}
        @if (session('success'))
            <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-700 font-bold ml-2">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-rose-100 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Konten Halaman --}}
        @yield('content')

    </main>

    {{-- Footer --}}
    <footer class="text-center text-xs text-slate-400 py-6 border-t border-slate-200 mt-auto">
        Smart Loker &copy; {{ date('Y') }}
    </footer>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getDatabase, ref, onValue, update, push, set, query, limitToLast, get } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-database.js";

        const firebaseConfig = {
            databaseURL: "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app"
        };

        const app = initializeApp(firebaseConfig);
        const database = getDatabase(app);

        const icon = document.getElementById('status-icon');
        if (icon) {
            const lockerRef = ref(database, 'locker_status');
            const logsRef = ref(database, 'logs');
            const recentLogsQuery = query(logsRef, limitToLast(10));
            
            let autoLockTimer;
            let wasLocked = true; 

            onValue(lockerRef, (snapshot) => {
                const data = snapshot.val();
                if (data) {
                    const text = document.getElementById('status-text');
                    const lastOpened = document.getElementById('last-opened');
                    const lastCard = document.getElementById('last-card');

                    if (data.terkunci) {
                        icon.innerHTML = '🔒';
                        icon.className = 'w-24 h-24 rounded-full flex items-center justify-center text-5xl mb-4 shadow-sm transition-colors duration-500 bg-emerald-50 text-emerald-500 ring-4 ring-emerald-50';
                        text.innerText = 'Terkunci';
                        text.className = 'text-3xl font-extrabold tracking-tight text-emerald-600 transition-colors duration-500';
                        wasLocked = true;
                    } else {
                        // KETIKA FIREBASE BERUBAH JADI TERBUKA
                        if (wasLocked) {
                            wasLocked = false;
                            
                            const now = new Date();
                            const waktuStr = now.toLocaleString('id-ID', {day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) + ' WIB';
                            
                            // CEK DULU DI WEB APAKAH KARTU INI TERDAFTAR?
                            get(ref(database, 'cards')).then((cardsSnapshot) => {
                                const cardsData = cardsSnapshot.val();
                                let isRegistered = false;
                                if (cardsData) {
                                    for (const key in cardsData) {
                                        if (cardsData[key].uid === data.kartu_terakhir) {
                                            isRegistered = true;
                                            break;
                                        }
                                    }
                                }

                                if (isRegistered) {
                                    // KARTU VALID!
                                    icon.innerHTML = '🔓';
                                    icon.className = 'w-24 h-24 rounded-full flex items-center justify-center text-5xl mb-4 shadow-sm transition-colors duration-500 bg-rose-50 text-rose-500 ring-4 ring-rose-50';
                                    text.innerText = 'Terbuka';
                                    text.className = 'text-3xl font-extrabold tracking-tight text-rose-600 transition-colors duration-500';

                                    update(lockerRef, { terakhir_buka: waktuStr });
                                    
                                    const newLogRef = push(logsRef);
                                    set(newLogRef, {
                                        waktu: waktuStr,
                                        kartu: data.kartu_terakhir || '-',
                                        hasil: 'berhasil',
                                        email: 'terkirim'
                                    });

                                    clearTimeout(autoLockTimer);
                                    autoLockTimer = setTimeout(() => {
                                        update(lockerRef, { terkunci: true });
                                    }, 5000);

                                } else {
                                    // KARTU TIDAK VALID! (Ditolak)
                                    // Arduino terlanjur kirim false, kita langsung paksa kembalikan ke true
                                    update(lockerRef, { terkunci: true });
                                    wasLocked = true; // Langsung anggap terkunci lagi
                                    
                                    // Catat sebagai akses ditolak
                                    const newLogRef = push(logsRef);
                                    set(newLogRef, {
                                        waktu: waktuStr,
                                        kartu: data.kartu_terakhir || '-',
                                        hasil: 'gagal',
                                        email: '-'
                                    });
                                }
                            });
                        }
                    }

                    if (data.terakhir_buka && lastOpened) {
                        lastOpened.innerText = data.terakhir_buka;
                    }
                    
                    if (data.kartu_terakhir && lastCard) {
                        lastCard.innerText = data.kartu_terakhir;
                    }
                }
            });

            // Listener untuk Riwayat Akses (Real-time Table)
            const tableBody = document.getElementById('logs-table-body');
            if (tableBody) {
                onValue(recentLogsQuery, (snapshot) => {
                    const logs = [];
                    snapshot.forEach((childSnapshot) => {
                        logs.push(childSnapshot.val());
                    });
                    
                    // Balik urutan agar yang terbaru di atas
                    logs.reverse();
                    
                    if (logs.length > 0) {
                        tableBody.innerHTML = '';
                        logs.forEach(log => {
                            const html = `
                                <tr class="hover:bg-slate-50/70 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-700 font-medium">${log.waktu}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-slate-600 text-xs bg-slate-100/50 px-2 py-1 rounded border border-slate-200/50 group-hover:bg-white transition-colors">
                                            ${log.kartu}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        ${log.hasil === 'berhasil' 
                                            ? `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span> Akses Diberikan</span>`
                                            : `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 shadow-[0_0_5px_rgba(244,63,94,0.5)]"></span> Akses Ditolak</span>`
                                        }
                                    </td>
                                    <td class="px-6 py-4">
                                        ${log.email === 'terkirim'
                                            ? `<span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Terkirim</span>`
                                            : `<span class="inline-flex items-center gap-1 text-rose-500 font-medium text-xs"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Gagal</span>`
                                        }
                                    </td>
                                </tr>
                            `;
                            tableBody.innerHTML += html;
                        });
                    }
                });
            }
        }
    </script>
</body>
</html>