<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Loker')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    </style>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] } } } }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    {{-- Overlay mobile --}}
    <div id="overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- SIDEBAR --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-slate-900 text-slate-300 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div class="px-7 py-8 flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-xl shadow-lg shadow-indigo-900/50">🔐</div>
            <div>
                <p class="text-white font-extrabold text-lg leading-tight tracking-tight">Smart Loker</p>
                <p class="text-[11px] text-slate-500 font-medium">RFID Access System</p>
            </div>
        </div>

        <nav class="flex-1 px-4 space-y-1.5">
            <p class="px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em]">Menu</p>

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>

            <a href="{{ route('pengaturan') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('pengaturan') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengaturan
            </a>
        </nav>

        <div class="m-4 p-4 rounded-2xl bg-slate-800/60 border border-slate-700/50">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2.5 w-2.5">
                    <span id="device-status-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                    <span id="device-status-dot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <p id="device-status-text" class="text-xs font-semibold text-slate-200">Sistem Aktif</p>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">Terhubung ke Firebase Realtime Database</p>
        </div>
    </aside>

    {{-- KONTEN --}}
    <div class="lg:pl-72 min-h-screen flex flex-col">

        {{-- Topbar mobile --}}
        <header class="lg:hidden sticky top-0 z-20 bg-white/80 backdrop-blur border-b border-slate-200 px-4 py-3 flex items-center gap-3">
            <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-slate-100" aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <p class="font-extrabold tracking-tight">Smart Loker</p>
        </header>

        <main class="flex-1 p-4 sm:p-8 lg:p-10 max-w-7xl w-full mx-auto">

            {{-- Notifikasi Flash --}}
            @if (session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-3 rounded-xl text-sm font-medium flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-700 font-bold ml-2">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-5 py-3 rounded-xl text-sm font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Konten Halaman --}}
            @yield('content')
        </main>

        <footer class="text-center text-xs text-slate-400 py-6 border-t border-slate-200 mt-auto">
            Smart Loker &copy; {{ date('Y') }}
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('overlay').classList.toggle('hidden');
        }
    </script>

    <script type="module">
        import { initializeApp, getApps, getApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getDatabase, ref, onValue, update, push, set, query, limitToLast, get, runTransaction, orderByChild } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-database.js";

        const firebaseConfig = {
            databaseURL: "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app"
        };

        const app = getApps().length === 0 ? initializeApp(firebaseConfig) : getApp();
        const database = getDatabase(app);

        const icon = document.getElementById('status-icon');
        if (icon) {
            const lockerRef = ref(database, 'locker_status');
            const logsRef = ref(database, 'logs');
            const recentLogsQuery = query(logsRef, orderByChild('timestamp'), limitToLast(10));

            let autoLockTimer;
            let wasLocked = true;

            // Tampilan status (disesuaikan dengan desain dashboard baru)
            const panel = document.getElementById('status-panel');
            const subtitle = document.getElementById('status-subtitle');

            function tampilTerkunci() {
                const text = document.getElementById('status-text');
                icon.innerHTML = '🔒';
                icon.className = 'w-28 h-28 rounded-full flex items-center justify-center text-5xl mb-5 shadow-inner transition-colors duration-500 bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50';
                text.innerText = 'Terkunci';
                text.className = 'text-3xl font-extrabold tracking-tight transition-colors duration-500 text-emerald-600';
                if (subtitle) subtitle.innerText = 'Loker aman';
                if (panel) panel.className = 'py-10 px-8 flex flex-col items-center text-center bg-gradient-to-b from-emerald-50/70 to-white';
            }

            function tampilTerbuka() {
                const text = document.getElementById('status-text');
                icon.innerHTML = '🔓';
                icon.className = 'w-28 h-28 rounded-full flex items-center justify-center text-5xl mb-5 shadow-inner transition-colors duration-500 bg-rose-100 text-rose-600 ring-8 ring-rose-50';
                text.innerText = 'Terbuka';
                text.className = 'text-3xl font-extrabold tracking-tight transition-colors duration-500 text-rose-600';
                if (subtitle) subtitle.innerText = 'Loker sedang dibuka';
                if (panel) panel.className = 'py-10 px-8 flex flex-col items-center text-center bg-gradient-to-b from-rose-50/70 to-white';
            }

            onValue(lockerRef, (snapshot) => {
                const data = snapshot.val();
                if (data) {
                    const lastOpened = document.getElementById('last-opened');
                    const lastCard = document.getElementById('last-card');

                    if (data.terkunci) {
                        tampilTerkunci();
                        wasLocked = true;
                    } else {
                        // KETIKA FIREBASE BERUBAH JADI TERBUKA
                        if (wasLocked) {
                            wasLocked = false;

                            // Mencegah duplikasi log jika web dibuka di banyak tab/perangkat
                            const processLockRef = ref(database, 'locker_status/process_lock');
                            runTransaction(processLockRef, (currentData) => {
                                const nowTs = Date.now();
                                if (currentData && (nowTs - currentData) < 5000) {
                                    return; // Batal, sudah diproses tab lain
                                }
                                return nowTs;
                            }).then((result) => {
                                if (!result.committed) return; // Batal

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
                                        tampilTerbuka();

                                        update(lockerRef, { terakhir_buka: waktuStr });

                                        const newLogRef = push(logsRef);
                                        set(newLogRef, {
                                            waktu: waktuStr,
                                            kartu: data.kartu_terakhir || '-',
                                            hasil: 'berhasil',
                                            email: 'terkirim',
                                            timestamp: Date.now() // Tambahkan Unix timestamp
                                        });

                                        clearTimeout(autoLockTimer);
                                        autoLockTimer = setTimeout(() => {
                                            update(lockerRef, { terkunci: true });
                                        }, 5000);

                                    } else {
                                        // KARTU TIDAK VALID! (Ditolak)
                                        update(lockerRef, { terkunci: true });
                                        wasLocked = true; // Langsung anggap terkunci lagi

                                        // Catat sebagai akses ditolak
                                        const newLogRef = push(logsRef);
                                        set(newLogRef, {
                                            waktu: waktuStr,
                                            kartu: data.kartu_terakhir || '-',
                                            hasil: 'gagal',
                                            email: '-',
                                            timestamp: Date.now() // Tambahkan Unix timestamp
                                        });
                                    }
                                });
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

                    // Update kartu ringkasan & jumlah aktivitas
                    const berhasil = logs.filter(l => l.hasil === 'berhasil').length;
                    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
                    setText('stat-total', logs.length);
                    setText('stat-berhasil', berhasil);
                    setText('stat-ditolak', logs.length - berhasil);
                    setText('log-count', logs.length + ' aktivitas');

                    if (logs.length > 0) {
                        let rows = '';
                        logs.forEach(log => {
                            rows += `
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-700 font-medium">${log.waktu}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-slate-600 text-xs bg-slate-100 px-2 py-1 rounded-md border border-slate-200/60">${log.kartu}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        ${log.hasil === 'berhasil'
                                            ? `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Akses Diberikan</span>`
                                            : `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Akses Ditolak</span>`
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
                        });
                        tableBody.innerHTML = rows;
                    }
                });
            }
        }

        // ==========================================
        // ONLINE / OFFLINE INDICATOR LOGIC (GLOBAL SIDEBAR)
        // ==========================================
        const pingRef = ref(database, 'locker_status/last_ping');
        let lastPingTime = 0;

        onValue(pingRef, (snapshot) => {
            const pingData = snapshot.val();
            if (pingData) {
                lastPingTime = parseInt(pingData);
            }
        });

        setInterval(() => {
            const statusPing = document.getElementById('device-status-ping');
            const statusDot = document.getElementById('device-status-dot');
            const statusText = document.getElementById('device-status-text');
            
            if (statusDot && statusText && lastPingTime > 0) {
                const currentUnix = Math.floor(Date.now() / 1000);
                const diff = currentUnix - lastPingTime;
                
                if (diff > 25) {
                    statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500';
                    if (statusPing) statusPing.className = 'hidden';
                    statusText.innerText = 'OFFLINE';
                    statusText.className = 'text-xs font-semibold text-rose-400';
                } else {
                    statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500';
                    if (statusPing) statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60';
                    statusText.innerText = 'Sistem Aktif (ONLINE)';
                    statusText.className = 'text-xs font-semibold text-slate-200';
                }
            }
        }, 2000); // Cek status setiap 2 detik
    </script>
</body>
</html>
