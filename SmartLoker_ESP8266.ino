#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP_Mail_Client.h>
#include <ArduinoJson.h>

// ======================
// WIFI
// ======================
#define WIFI_SSID "nan"
#define WIFI_PASSWORD "nando1234567"

// ======================
// FIREBASE
// ======================
const char* FIREBASE_HOST = "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app";

// ======================
// EMAIL (SMTP)
// ======================
#define SMTP_HOST "smtp.gmail.com"
#define SMTP_PORT 465
#define AUTHOR_EMAIL "EMAIL_PENGIRIM@gmail.com" // Ganti dengan email Gmail Anda
#define AUTHOR_PASSWORD "SANDI_APLIKASI" // Ganti dengan Sandi Aplikasi (App Password) Gmail Anda

// ======================
// PIN & HARDWARE
// ======================
#define SS_PIN D8
#define RST_PIN D3
#define RELAY_PIN D1
#define BUZZER_PIN D2
#define LED_HIJAU_PIN D4
#define LED_MERAH_PIN D5

MFRC522 rfid(SS_PIN, RST_PIN);
WiFiClientSecure secureClient;
SMTPSession smtp;

// Deklarasi fungsi
void kirimUpdateStatus(String uid);
bool cekKartuTerdaftar(String uid, String &namaPemilik);
void ambilEmailDanKirim(String uid, String namaPemilik);
void bukaLoker();
void tolakAkses();

String firebaseURL(String path) {
    return String(FIREBASE_HOST) + path + ".json";
}

void setup() {
    Serial.begin(115200);

    pinMode(RELAY_PIN, OUTPUT);
    pinMode(BUZZER_PIN, OUTPUT);
    pinMode(LED_HIJAU_PIN, OUTPUT);
    pinMode(LED_MERAH_PIN, OUTPUT);

    // Kondisi awal (Relay HIGH/LOW disesuaikan dengan jenis relay Anda)
    digitalWrite(RELAY_PIN, HIGH); // Misal HIGH = Loker Terkunci
    digitalWrite(LED_HIJAU_PIN, LOW);
    digitalWrite(LED_MERAH_PIN, HIGH); // LED Merah menyala standby

    SPI.begin();
    rfid.PCD_Init();

    Serial.println();
    Serial.println("Memulai Smart Loker...");

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
    Serial.print("Menghubungkan WiFi");

    while (WiFi.status() != WL_CONNECTED) {
        Serial.print(".");
        delay(500);
    }

    Serial.println();
    Serial.println("WiFi terhubung");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());

    // Sinkronisasi waktu dari internet (NTP) wajib untuk SSL/TLS Gmail
    Serial.print("Menyelaraskan waktu");
    configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");
    while (time(nullptr) < 100000) {
        Serial.print(".");
        delay(500);
    }
    Serial.println("\nWaktu berhasil disinkronisasi!");

    // Penting untuk koneksi ke Firebase (HTTPS)
    secureClient.setInsecure(); 
}

void loop() {
    // Tunggu ada kartu ditempel
    if (!rfid.PICC_IsNewCardPresent()) return;
    if (!rfid.PICC_ReadCardSerial()) return;

    String uid = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
        if (rfid.uid.uidByte[i] < 0x10) uid += "0";
        uid += String(rfid.uid.uidByte[i], HEX);
    }
    uid.toUpperCase();

    Serial.print("Kartu Ditempel - UID: ");
    Serial.println(uid);

    // 1. Selalu kirim UID ke Firebase agar Web Laravel bisa mendeteksi saat mau Tambah Kartu
    updateKartuTerakhir(uid);

    // 2. Cek apakah kartu terdaftar di Firebase
    String namaPemilik = "";
    if (cekKartuTerdaftar(uid, namaPemilik)) {
        Serial.println("Akses Diberikan untuk: " + namaPemilik);
        
        // 3. Update status Firebase menjadi terbuka
        updateStatusKunci(false);
        
        // 4. Buka loker secara fisik (Relay dll) - ini akan menahan selama 5 detik lalu mengunci lagi
        bukaLoker();
        
        // 5. Update status Firebase menjadi terkunci KEMBALI
        updateStatusKunci(true);
        
        // 6. Ambil daftar email dari Firebase dan kirim notifikasi
        ambilEmailDanKirim(uid, namaPemilik);
    } else {
        Serial.println("Akses Ditolak: Kartu tidak terdaftar.");
        tolakAkses();
    }

    // Halt & Stop Crypto
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(2000); // Jeda sebelum bisa scan ulang
}

// ==========================================
// FUNGSI: Update Kartu Terakhir ke Firebase (Untuk Web)
// ==========================================
void updateKartuTerakhir(String uid) {
    HTTPClient http;
    http.begin(secureClient, firebaseURL("/locker_status/kartu_terakhir"));
    http.addHeader("Content-Type", "application/json");

    String payload = "\"" + uid + "\"";
    int httpCode = http.PUT(payload);
    if (httpCode > 0) {
        Serial.println("UID terbaru berhasil diupdate ke Firebase");
    }
    http.end();
    // Tidak perlu memanggil secureClient.stop() di sini jika segera disusul request lain,
    // tapi aman jika memori terbatas.
}

// ==========================================
// FUNGSI: Update Status Gembok ke Firebase (Realtime Dashboard)
// ==========================================
void updateStatusKunci(bool terkunci) {
    HTTPClient http;
    http.begin(secureClient, firebaseURL("/locker_status/terkunci"));
    http.addHeader("Content-Type", "application/json");

    String payload = terkunci ? "true" : "false";
    int httpCode = http.PUT(payload);
    if (httpCode > 0) {
        Serial.println(terkunci ? "Status Loker: TERKUNCI (Firebase)" : "Status Loker: TERBUKA (Firebase)");
    }
    http.end();
}

// ==========================================
// FUNGSI: Cek Kartu Terdaftar di Firebase
// ==========================================
bool cekKartuTerdaftar(String uid, String &namaPemilik) {
    HTTPClient http;
    http.begin(secureClient, firebaseURL("/cards"));
    int httpCode = http.GET();
    
    bool terdaftar = false;

    if (httpCode == HTTP_CODE_OK) {
        String payload = http.getString();
        
        DynamicJsonDocument doc(2048);
        DeserializationError error = deserializeJson(doc, payload);
        
        if (!error && doc.is<JsonObject>()) {
            JsonObject cards = doc.as<JsonObject>();
            
            // Loop semua data kartu di Firebase
            for (JsonPair kv : cards) {
                String registeredUid = kv.value()["uid"].as<String>();
                if (registeredUid == uid) {
                    terdaftar = true;
                    namaPemilik = kv.value()["nama"].as<String>();
                    break;
                }
            }
        }
    }
    http.end();
    return terdaftar;
}

// ==========================================
// FUNGSI: Ambil Email dan Kirim Notifikasi
// ==========================================
void ambilEmailDanKirim(String uid, String namaPemilik) {
    HTTPClient http;
    http.begin(secureClient, firebaseURL("/emails"));
    int httpCode = http.GET();
    
    String payload = "";
    if (httpCode == HTTP_CODE_OK) {
        payload = http.getString();
    }
    
    // TUTUP HTTP SEGERA UNTUK MEMBEBASKAN RAM / MEMORI SSL!
    http.end();
    
    if (payload != "") {
        DynamicJsonDocument doc(2048);
        DeserializationError error = deserializeJson(doc, payload);
        
        if (!error && doc.is<JsonObject>()) {
            JsonObject emails = doc.as<JsonObject>();
            
            // Aktifkan debug SMTP untuk melihat detail error di Serial Monitor jika gagal
            smtp.debug(1);

            // Konfigurasi SMTP
            Session_Config config;
            config.server.host_name = SMTP_HOST;
            config.server.port = SMTP_PORT;
            config.login.email = AUTHOR_EMAIL;
            config.login.password = AUTHOR_PASSWORD;
            config.login.user_domain = "";

            SMTP_Message message;
            message.sender.name = "Smart Loker System";
            message.sender.email = AUTHOR_EMAIL;
            message.subject = "Pemberitahuan: Loker Dibuka";
            
            // Format isi Email
            String htmlMsg = "<h2>Loker Telah Dibuka</h2><p>Loker baru saja dibuka oleh:</p><ul><li><b>Nama:</b> " + namaPemilik + "</li><li><b>UID:</b> " + uid + "</li></ul>";
            message.html.content = htmlMsg.c_str();

            // Tambahkan semua email penerima dari Firebase
            for (JsonPair kv : emails) {
                String alamat = kv.value()["alamat"].as<String>();
                message.addRecipient(alamat.c_str(), alamat.c_str());
                Serial.println("Email penerima ditemukan: " + alamat);
            }

            // Mulai Kirim Email
            Serial.println("Menghubungkan ke server SMTP...");
            if (!smtp.connect(&config)) {
                Serial.println("Gagal terhubung ke SMTP server.");
                return;
            }

            Serial.println("Sedang mengirim email...");
            if (!MailClient.sendMail(&smtp, &message)) {
                Serial.println("Error kirim email: " + smtp.errorReason());
            } else {
                Serial.println("Email notifikasi berhasil dikirim ke seluruh penerima!");
            }
        } else {
            Serial.println("Gagal parse email dari Firebase (atau kosong).");
        }
    } else {
        Serial.println("Gagal mengambil email dari Firebase. HTTP Code: " + String(httpCode));
    }
}

// ==========================================
// FUNGSI: Aksi Buka Loker
// ==========================================
void bukaLoker() {
    digitalWrite(LED_MERAH_PIN, LOW);
    digitalWrite(LED_HIJAU_PIN, HIGH);
    
    // Bunyi Beep Berhasil (2x cepat)
    tone(BUZZER_PIN, 2000, 100); delay(150);
    tone(BUZZER_PIN, 2000, 100);
    
    digitalWrite(RELAY_PIN, LOW); // Loker terbuka (Sesuaikan HIGH/LOW relay)
    Serial.println("Solenoid Terbuka (5 detik)");
    delay(5000); // Loker dibiarkan terbuka selama 5 detik
    
    digitalWrite(RELAY_PIN, HIGH); // Loker dikunci kembali
    Serial.println("Solenoid Dikunci Kembali");
    
    digitalWrite(LED_HIJAU_PIN, LOW);
    digitalWrite(LED_MERAH_PIN, HIGH);
}

// ==========================================
// FUNGSI: Aksi Akses Ditolak
// ==========================================
void tolakAkses() {
    // Bunyi Beep Gagal (panjang)
    tone(BUZZER_PIN, 500, 1000);
    
    // Kedip merah 3x
    for(int i=0; i<3; i++) {
        digitalWrite(LED_MERAH_PIN, LOW);
        delay(200);
        digitalWrite(LED_MERAH_PIN, HIGH);
        delay(200);
    }
}

