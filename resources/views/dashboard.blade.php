@extends('layouts.app')

@section('title', 'Dashboard - Smart Loker')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- BAGIAN KIRI: STATUS LOKER (1 Kolom) --}}
        <div class="lg:col-span-1 space-y-8">
            <section class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden hover:shadow-md transition-shadow">
                <div class="bg-slate-50/50 p-6 border-b border-slate-100">
                    <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Status Loker Saat Ini</h2>
                </div>
                
                <div class="p-8 flex flex-col items-center justify-center text-center">
                    <div id="status-icon" class="w-24 h-24 rounded-full flex items-center justify-center text-5xl mb-4 shadow-sm transition-colors duration-500
                        {{ $locker['terkunci'] ? 'bg-emerald-50 text-emerald-500 ring-4 ring-emerald-50' : 'bg-rose-50 text-rose-500 ring-4 ring-rose-50' }}">
                        {{ $locker['terkunci'] ? '🔒' : '🔓' }}
                    </div>
                    
                    <p id="status-text" class="text-3xl font-extrabold tracking-tight {{ $locker['terkunci'] ? 'text-emerald-600' : 'text-rose-600' }} transition-colors duration-500">
                        {{ $locker['terkunci'] ? 'Terkunci' : 'Terbuka' }}
                    </p>
                    <p class="text-sm text-slate-400 mt-2 font-medium">Loker #{{ $locker['nomor'] }}</p>
                </div>

                <div class="bg-slate-50 p-6 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Terakhir dibuka</p>
                        <p class="text-xs text-slate-400">Kartu RFID</p>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p id="last-opened" class="font-bold text-slate-700">{{ $locker['terakhir_buka'] }}</p>
                        <span id="last-card" class="font-mono bg-white border border-slate-200 px-2 py-1 rounded-md text-slate-600 text-xs shadow-sm">{{ $locker['kartu_terakhir'] }}</span>
                    </div>
                </div>
            </section>
        </div>

        {{-- BAGIAN KANAN: RIWAYAT AKSES (2 Kolom) --}}
        <div class="lg:col-span-2 space-y-8">
            <section class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden hover:shadow-md transition-shadow flex flex-col h-full">
                <div class="bg-slate-50/50 p-6 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Riwayat Akses Terakhir</h2>
                    <span class="text-xs font-medium text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200">{{ count($logs) }} aktivitas</span>
                </div>

                <div class="overflow-x-auto p-2 flex-1">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-slate-400 text-[11px] uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Waktu</th>
                                <th class="px-6 py-4 font-semibold">Kartu RFID</th>
                                <th class="px-6 py-4 font-semibold">Hasil</th>
                                <th class="px-6 py-4 font-semibold">Notifikasi Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80" id="logs-table-body">
                            @forelse ($logs as $log)
                                <tr class="hover:bg-slate-50/70 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap text-slate-700 font-medium">{{ $log['waktu'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-slate-600 text-xs bg-slate-100/50 px-2 py-1 rounded border border-slate-200/50 group-hover:bg-white transition-colors">
                                            {{ $log['kartu'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($log['hasil'] === 'berhasil')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span> Akses Diberikan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shadow-[0_0_5px_rgba(244,63,94,0.5)]"></span> Akses Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($log['email'] === 'terkirim')
                                            <span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Terkirim
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-rose-500 font-medium text-xs">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
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