@extends('layouts.app')

@section('title', 'Dashboard - Smart Loker')

@section('content')
@php
    $koleksi = collect($logs);
    $totalLog = $koleksi->count();
    $totalBerhasil = $koleksi->where('hasil', 'berhasil')->count();
    $totalDitolak = $totalLog - $totalBerhasil;
@endphp

    {{-- HEADER --}}
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">Pantau status loker dan riwayat akses secara real-time.</p>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Aktivitas</p>
                <p class="text-2xl font-extrabold text-slate-900" id="stat-total">{{ $totalLog }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Akses Diberikan</p>
                <p class="text-2xl font-extrabold text-emerald-600" id="stat-berhasil">{{ $totalBerhasil }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200/70 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Akses Ditolak</p>
                <p class="text-2xl font-extrabold text-rose-600" id="stat-ditolak">{{ $totalDitolak }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- STATUS LOKER --}}
        <div class="xl:col-span-1">
            <section class="bg-white rounded-3xl shadow-sm border border-slate-200/70 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Status Loker Saat Ini</h2>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">Loker #{{ $locker['nomor'] }}</span>
                </div>

                <div class="py-10 px-8 flex flex-col items-center text-center
                    {{ $locker['terkunci'] ? 'bg-gradient-to-b from-emerald-50/70 to-white' : 'bg-gradient-to-b from-rose-50/70 to-white' }}" id="status-panel">
                    <div id="status-icon" class="w-28 h-28 rounded-full flex items-center justify-center text-5xl mb-5 shadow-inner transition-colors duration-500
                        {{ $locker['terkunci'] ? 'bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50' : 'bg-rose-100 text-rose-600 ring-8 ring-rose-50' }}">
                        {{ $locker['terkunci'] ? '🔒' : '🔓' }}
                    </div>
                    <p id="status-text" class="text-3xl font-extrabold tracking-tight transition-colors duration-500 {{ $locker['terkunci'] ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $locker['terkunci'] ? 'Terkunci' : 'Terbuka' }}
                    </p>
                    <p id="status-subtitle" class="text-sm text-slate-400 mt-1 font-medium">{{ $locker['terkunci'] ? 'Loker aman' : 'Loker sedang dibuka' }}</p>
                </div>

                <div class="px-6 py-5 border-t border-slate-100 bg-slate-50/60 space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir dibuka</p>
                        <p id="last-opened" class="font-bold text-slate-800 mt-0.5">{{ $locker['terakhir_buka'] }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kartu RFID</p>
                        <span id="last-card" class="inline-block mt-1 font-mono bg-white border border-slate-200 px-2.5 py-1 rounded-lg text-slate-600 text-xs shadow-sm">{{ $locker['kartu_terakhir'] }}</span>
                    </div>
                </div>
            </section>
        </div>

        {{-- RIWAYAT AKSES --}}
        <div class="xl:col-span-2">
            <section class="bg-white rounded-3xl shadow-sm border border-slate-200/70 overflow-hidden flex flex-col h-full">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Riwayat Akses Terakhir</h2>
                        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full" id="log-count">{{ count($logs) }} aktivitas</span>
                    </div>
                    <button onclick="downloadLaporan()" class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export Laporan
                    </button>
                </div>

                <script>
                    function downloadLaporan() {
                        let csv = 'Waktu,Kartu RFID,Hasil,Notifikasi Email\n';
                        const rows = document.querySelectorAll('#logs-table-body tr');

                        if (rows.length === 0 || rows[0].innerText.includes('Belum ada aktivitas')) {
                            alert('Belum ada data untuk diexport.');
                            return;
                        }

                        rows.forEach(row => {
                            const cols = row.querySelectorAll('td');
                            if (cols.length === 4) {
                                const waktu = cols[0].innerText.trim();
                                const rfid = cols[1].innerText.trim();
                                const hasil = cols[2].innerText.replace('Akses Diberikan', 'Diberikan').replace('Akses Ditolak', 'Ditolak').trim();
                                const email = cols[3].innerText.trim();
                                csv += `"${waktu}","${rfid}","${hasil}","${email}"\n`;
                            }
                        });

                        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'Laporan_Akses_SmartLoker.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }
                </script>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-slate-400 text-[11px] uppercase tracking-wider bg-slate-50/60">
                                <th class="px-6 py-3.5 font-semibold">Waktu</th>
                                <th class="px-6 py-3.5 font-semibold">Kartu RFID</th>
                                <th class="px-6 py-3.5 font-semibold">Hasil</th>
                                <th class="px-6 py-3.5 font-semibold">Notifikasi Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="logs-table-body">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-700 font-medium">{{ $log['waktu'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-slate-600 text-xs bg-slate-100 px-2 py-1 rounded-md border border-slate-200/60">{{ $log['kartu'] }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($log['hasil'] === 'berhasil')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Akses Diberikan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Akses Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($log['email'] === 'terkirim')
                                            <span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Terkirim
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-rose-500 font-medium text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-14 text-center text-slate-400 text-sm">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <p>Belum ada aktivitas tercatat.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    </div>

@endsection
