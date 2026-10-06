<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Ambil data status loker
    $lockerUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/locker_status.json';
    $lockerResponse = \Illuminate\Support\Facades\Http::get($lockerUrl);
    
    $locker = [
        'nomor'         => 12,
        'terkunci'      => true,
        'terakhir_buka' => '-',
        'kartu_terakhir'=> '-',
    ];

    if ($lockerResponse->successful() && $lockerResponse->json()) {
        $data = $lockerResponse->json();
        $locker['terkunci'] = $data['terkunci'] ?? true;
        $locker['terakhir_buka'] = $data['terakhir_buka'] ?? '-';
        $locker['kartu_terakhir'] = $data['kartu_terakhir'] ?? '-';
    }

    // Ambil data riwayat log (hanya 10 terakhir)
    $logsUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/logs.json?orderBy="$key"&limitToLast=10';
    $logsResponse = \Illuminate\Support\Facades\Http::get($logsUrl);
    
    $logs = [];
    if ($logsResponse->successful() && $logsResponse->json()) {
        $logsData = $logsResponse->json();
        // Firebase me-return object, kita ubah jadi array dan reverse agar terbaru di atas
        foreach ($logsData as $key => $log) {
            $logs[] = [
                'waktu' => $log['waktu'] ?? '-',
                'kartu' => $log['kartu'] ?? '-',
                'hasil' => $log['hasil'] ?? '-',
                'email' => $log['email'] ?? '-'
            ];
        }
        $logs = array_reverse($logs); // Terbaru di atas
    }

    return view('dashboard', compact('locker', 'logs'));
})->name('dashboard');

Route::get('/pengaturan', function () {
    $locker = ['nomor' => 12];
    
    // Ambil data kartu dari Firebase
    $cardsUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/cards.json';
    $cardsResponse = \Illuminate\Support\Facades\Http::get($cardsUrl);
    
    $cards = [];
    if ($cardsResponse->successful() && $cardsResponse->json()) {
        foreach ($cardsResponse->json() as $id => $data) {
            $cards[] = [
                'id' => $id,
                'uid' => $data['uid'] ?? '',
                'nama' => $data['nama'] ?? ''
            ];
        }
    }
    
    // Ambil data email dari Firebase
    $firebaseUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/emails.json';
    $response = \Illuminate\Support\Facades\Http::get($firebaseUrl);
    
    $emails = [];
    if ($response->successful() && $response->json()) {
        foreach ($response->json() as $id => $data) {
            $emails[] = [
                'id' => $id,
                'alamat' => $data['alamat']
            ];
        }
    }

    return view('pengaturan', compact('locker', 'cards', 'emails'));
})->name('pengaturan');

Route::post('/pengaturan/email', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);
    
    $firebaseUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/emails.json';
    \Illuminate\Support\Facades\Http::post($firebaseUrl, [
        'alamat' => $request->email
    ]);

    return back()->with('success', 'Email notifikasi berhasil ditambahkan ke Firebase!');
})->name('email.store');

Route::delete('/pengaturan/email/{id}', function ($id) {
    $firebaseUrl = "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/emails/{$id}.json";
    \Illuminate\Support\Facades\Http::delete($firebaseUrl);

    return back()->with('success', 'Email notifikasi berhasil dihapus dari Firebase!');
})->name('email.destroy');

Route::post('/kartu', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'nama' => 'required',
        'uid' => 'required'
    ]);

    $firebaseUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/cards.json';
    \Illuminate\Support\Facades\Http::post($firebaseUrl, [
        'nama' => $request->nama,
        'uid' => $request->uid
    ]);

    return back()->with('success', 'Kartu berhasil ditambahkan!');
})->name('kartu.store');

Route::delete('/kartu/{id}', function ($id) {
    $firebaseUrl = "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/cards/{$id}.json";
    \Illuminate\Support\Facades\Http::delete($firebaseUrl);

    return back()->with('success', 'Kartu berhasil dihapus!');
})->name('kartu.destroy');

Route::post('/rfid-tap', function (\Illuminate\Http\Request $request) {
    $uid = $request->input('uid');
    
    if (!$uid) {
        return response()->json(['error' => 'UID kosong'], 400);
    }

    $waktu = now()->format('d M Y, H:i') . ' WIB';

    // Loop semua email dari database (dummy here)
    $emailsTujuan = ['pemilik@email.com', 'admin@email.com']; 
    foreach($emailsTujuan as $emailTujuan) {
        try {
            \Illuminate\Support\Facades\Mail::to($emailTujuan)->send(new \App\Mail\LockerAccessed($uid, $waktu));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal kirim email: ' . $e->getMessage());
        }
    }

    $firebaseUrl = 'https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app/locker_status.json';
    
    $data = [
        'terkunci' => false,
        'terakhir_buka' => $waktu,
        'kartu_terakhir' => $uid,
    ];

    \Illuminate\Support\Facades\Http::put($firebaseUrl, $data);

    return response()->json(['success' => true, 'message' => 'Loker dibuka, email terkirim.']);
});
