<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smartlocker - Firebase Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        h1 { margin-top: 0; color: #f53003; }
        .status { margin-top: 20px; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
        .data-box {
            margin-top: 20px;
            background: #2d3748;
            color: #a0aec0;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            text-align: left;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Laravel + Firebase</h1>
        <p>Menghubungkan ke Realtime Database...</p>
        <div id="status" class="status">Memproses...</div>
        
        <div id="dataBox" class="data-box" style="display: none;">
            Menunggu data...
        </div>
    </div>

    <!-- Firebase JS SDK as ES modules -->
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
        import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-analytics.js";
        import { getDatabase, ref, set, onValue } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-database.js";

        // Konfigurasi Firebase dari pengguna (dengan tambahan databaseURL untuk region asia-southeast1)
        const firebaseConfig = {
            apiKey: "AIzaSyDtkGRXR8L_rmnDSkYbGvL_7G0QItVax28",
            authDomain: "smartlocker-96fe1.firebaseapp.com",
            projectId: "smartlocker-96fe1",
            storageBucket: "smartlocker-96fe1.firebasestorage.app",
            messagingSenderId: "1055053296910",
            appId: "1:1055053296910:web:70812f1c7e23dc22db935f",
            measurementId: "G-90VV01D5HG",
            databaseURL: "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app"
        };

        // Inisialisasi Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
        const database = getDatabase(app);

        const statusDiv = document.getElementById('status');
        const dataBox = document.getElementById('dataBox');
  *abase
            const testRef = ref(database, 'test_connection');
            
            // Menulis data untuk tes koneksia
            set(testRef, {
                connected_at: new Date().toISOString(),
                message: "Halo dari aplikasi Laravel 12!",
                project: "Smartlocker"
            }).then(() => {
                statusDiv.innerHTML = "<span class='success'>Berhasil terhubung dan menulis ke Realtime Database!</span>";
                dataBox.style.display = 'block';
            }).catch((error) => {
                statusDiv.innerHTML = "<span class='error'>Gagal menulis ke database: " + error.message + "</span>";
            });

            // Membaca data secara realtime
            onValue(testRef, (snapshot) => {
                const data = snapshot.val();
                if (data) {
                    dataBox.innerText = JSON.stringify(data, null, 2);
                    console.log("Data diterima:", data);
                }
            });
            
        } catch (error) {
            statusDiv.innerHTML = "<span class='error'>Error koneksi: " + error.message + "</span>";
        }
    </script>
</body>
</html>
