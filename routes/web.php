<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $locker = [
        'nomor'         => 12,
        'terkunci'      => true,
        'terakhir_buka' => '6 Okt 2026, 19:58 WIB',
        'kartu_terakhir'=> '****A3F2',
    ];

    $logs = [
        ['waktu' => '6 Okt 2026, 19:58', 'kartu' => '****A3F2', 'hasil' => 'berhasil', 'email' => 'terkirim'],
        ['waktu' => '6 Okt 2026, 14:20', 'kartu' => '****A3F2', 'hasil' => 'berhasil', 'email' => 'terkirim'],
    ];

    $cards = [
        ['id' => 1, 'uid' => '****A3F2', 'nama' => 'Kartu utama'],
    ];

    $notifEmail = 'pemilik@email.com';

    return view('smart-loker', compact('locker', 'logs', 'cards', 'notifEmail'));
});
