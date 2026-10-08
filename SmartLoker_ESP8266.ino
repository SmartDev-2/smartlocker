#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ESP_Mail_Client.h>
#include <ArduinoJson.h>
#include <time.h>

// ======================
// WIFI
// ======================
#define WIFI_SSID "nan"
#define WIFI_PASSWORD "ISI_PASSWORD_WIFI"

// ======================
// FIREBASE
// ======================
const char* FIREBASE_HOST = "https://smartlocker-96fe1-default-rtdb.asia-southeast1.firebasedatabase.app";

// ======================
// EMAIL (SMTP)
// ======================
#define SMTP_HOST "smtp.gmail.com"
#define SMTP_PORT 465   // jika gagal terus, coba 587
#define AUTHOR_EMAIL "akunalif774@gmail.com"
#define AUTHOR_PASSWORD "ISI_APP_PASSWORD_16_KARAKTER"  // tanpa spasi

#define MAX_RECIPIENTS 5

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

// Jenis notifikasi email
enum JenisNotif { LOKER_DIBUKA, AKTIVITAS_MENCURIGAKAN };

// Deklarasi fungsi
void kirimUpdateStatus(String uid);
bool cekKartuTerdaftar(String uid, String &namaPemilik);
void kirimNotifikasiEmail(JenisNotif jenis, String uid, String namaPemilik);
String waktuSekarang();
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

    digitalWrite(RELAY_PIN, HIGH);      // HIGH = terkunci
    digitalWrite(LED_HIJAU_PIN, LOW);
    digitalWrite(LED_MERAH_PIN, HIGH);  // LED merah standby

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

    // Sinkronisasi waktu (NTP), wajib untuk SSL/TLS Gmail
    Serial.print("Menyelaraskan waktu");
    configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");
    while (time(nullptr) < 100000) {
        Serial.print(".");
        delay(500);
    }
    Serial.println("\nWaktu berhasil disinkronisasi!");

    secureClient.setInsecure();  // koneksi HTTPS ke Firebase

    Serial.printf("Free heap awal: %u bytes\n", ESP.getFreeHeap());
}

unsigned long lastHeartbeat = 0;

void loop() {
    // Kirim sinyal "Online" (heartbeat) setiap 15 detik
    if (millis() - lastHeartbeat > 15000 || lastHeartbeat == 0) {
        lastHeartbeat = millis();
        HTTPClient http;
        http.begin(secureClient, firebaseURL("/locker_status/last_ping"));
        http.addHeader("Content-Type", "application/json");
        // Kirim UNIX Timestamp saat ini
        String payload = String(time(nullptr)); 
        http.PUT(payload);
        http.end();
    }

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

    // 1. Kirim UID ke Firebase (untuk fitur Tambah Kartu di web Laravel)
    kirimUpdateStatus(uid);

    // 2. Cek kartu terdaftar
    String namaPemilik = "";
    if (cekKartuTerdaftar(uid, namaPemilik)) {
        Serial.println("Akses Diberikan untuk: " + namaPemilik);

        bukaLoker();
        kirimNotifikasiEmail(LOKER_DIBUKA, uid, namaPemilik);
    } else {
        Serial.println("Akses Ditolak: Kartu tidak terdaftar.");

        tolakAkses();
        kirimNotifikasiEmail(AKTIVITAS_MENCURIGAKAN, uid, "Tidak dikenal");
    }

    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(2000);
}

// ==========================================
// Update status ke Firebase
// ==========================================
void kirimUpdateStatus(String uid) {
    HTTPClient http;
    http.begin(secureClient, firebaseURL("/locker_status"));
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<200> doc;
    doc["terkunci"] = false;
    doc["kartu_terakhir"] = uid;

    String jsonStr;
    serializeJson(doc, jsonStr);

    int httpCode = http.PUT(jsonStr);
    if (httpCode > 0) {
        Serial.println("Status Loker & UID berhasil diupdate ke Firebase");
    } else {
        Serial.println("Gagal update status: " + http.errorToString(httpCode));
    }
    http.end();
    secureClient.stop();
}

// ==========================================
// Cek kartu terdaftar di Firebase
// ==========================================
bool cekKartuTerdaftar(String uid, String &namaPemilik) {
    bool terdaftar = false;

    HTTPClient http;
    http.begin(secureClient, firebaseURL("/cards"));
    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
        String payload = http.getString();
        http.end();

        DynamicJsonDocument doc(2048);
        DeserializationError error = deserializeJson(doc, payload);

        if (!error && doc.is<JsonObject>()) {
            for (JsonPair kv : doc.as<JsonObject>()) {
                String registeredUid = kv.value()["uid"].as<String>();
                if (registeredUid == uid) {
                    terdaftar = true;
                    namaPemilik = kv.value()["nama"].as<String>();
                    break;
                }
            }
        }
    } else {
        http.end();
        Serial.println("Gagal ambil data kartu. HTTP Code: " + String(httpCode));
    }

    secureClient.stop();
    return terdaftar;
}

// ==========================================
// Waktu sekarang (WIB) untuk isi email
// ==========================================
String waktuSekarang() {
    time_t now = time(nullptr);
    struct tm* t = localtime(&now);
    char buf[25];
    strftime(buf, sizeof(buf), "%d-%m-%Y %H:%M:%S", t);
    return String(buf) + " WIB";
}

// ==========================================
// Ambil email dari Firebase lalu kirim notifikasi
// ==========================================
void kirimNotifikasiEmail(JenisNotif jenis, String uid, String namaPemilik) {
    String penerima[MAX_RECIPIENTS];
    int jumlah = 0;

    // TAHAP 1: ambil email dari Firebase, lalu bebaskan semua memori
    {
        HTTPClient http;
        http.begin(secureClient, firebaseURL("/emails"));
        int httpCode = http.GET();

        if (httpCode == HTTP_CODE_OK) {
            String payload = http.getString();
            http.end();

            DynamicJsonDocument doc(1024);
            if (!deserializeJson(doc, payload) && doc.is<JsonObject>()) {
                for (JsonPair kv : doc.as<JsonObject>()) {
                    if (jumlah >= MAX_RECIPIENTS) break;
                    String alamat = kv.value()["alamat"].as<String>();
                    if (alamat.length() > 0 && alamat != "null") {
                        penerima[jumlah++] = alamat;
                    }
                }
            }
        } else {
            http.end();
            Serial.println("Gagal ambil email. HTTP Code: " + String(httpCode));
        }
    }

    if (jumlah == 0) {
        Serial.println("Tidak ada email penerima.");
        secureClient.stop();
        return;
    }

    // TAHAP 2: tutup koneksi SSL Firebase agar heap lega
    secureClient.stop();
    delay(200);
    Serial.printf("Free heap sebelum SMTP: %u bytes\n", ESP.getFreeHeap());

    // TAHAP 3: susun dan kirim email
    smtp.debug(1);

    Session_Config config;
    config.server.host_name = SMTP_HOST;
    config.server.port = SMTP_PORT;
    config.login.email = AUTHOR_EMAIL;
    config.login.password = AUTHOR_PASSWORD;
    config.login.user_domain = "";
    config.time.ntp_server = "pool.ntp.org,time.nist.gov";
    config.time.gmt_offset = 7;
    config.time.day_light_offset = 0;

    SMTP_Message message;
    message.sender.name = "Smart Loker System";
    message.sender.email = AUTHOR_EMAIL;

    String htmlMsg;
    if (jenis == LOKER_DIBUKA) {
        message.subject = "Pemberitahuan: Loker Dibuka";
        htmlMsg = "<h2>Loker Telah Dibuka</h2>"
                  "<p>Loker baru saja dibuka oleh:</p>"
                  "<ul><li><b>Nama:</b> " + namaPemilik + "</li>"
                  "<li><b>UID:</b> " + uid + "</li>"
                  "<li><b>Waktu:</b> " + waktuSekarang() + "</li></ul>";
    } else {
        message.subject = "PERINGATAN: Aktivitas Mencurigakan pada Loker";
        htmlMsg = "<h2 style='color:red;'>Aktivitas Mencurigakan Terdeteksi!</h2>"
                  "<p>Ada kartu <b>tidak terdaftar</b> yang mencoba membuka loker:</p>"
                  "<ul><li><b>UID Kartu:</b> " + uid + "</li>"
                  "<li><b>Waktu:</b> " + waktuSekarang() + "</li>"
                  "<li><b>Status:</b> Akses ditolak</li></ul>"
                  "<p>Loker tetap terkunci. Periksa area loker jika diperlukan.</p>";
    }
    message.html.content = htmlMsg.c_str();

    for (int i = 0; i < jumlah; i++) {
        message.addRecipient(penerima[i].c_str(), penerima[i].c_str());
        Serial.println("Penerima: " + penerima[i]);
    }

    Serial.println("Menghubungkan ke server SMTP...");
    if (!smtp.connect(&config)) {
        Serial.println("Gagal konek SMTP: " + smtp.errorReason());
        return;
    }

    Serial.println("Sedang mengirim email...");
    if (!MailClient.sendMail(&smtp, &message)) {
        Serial.println("Error kirim email: " + smtp.errorReason());
    } else {
        Serial.println("Email notifikasi berhasil dikirim!");
    }

    smtp.closeSession();
    Serial.printf("Free heap setelah SMTP: %u bytes\n", ESP.getFreeHeap());
}

// ==========================================
// Aksi buka loker
// ==========================================
void bukaLoker() {
    digitalWrite(LED_MERAH_PIN, LOW);
    digitalWrite(LED_HIJAU_PIN, HIGH);

    tone(BUZZER_PIN, 2000, 100); delay(150);
    tone(BUZZER_PIN, 2000, 100);

    digitalWrite(RELAY_PIN, LOW);  // terbuka
    Serial.println("Solenoid Terbuka (5 detik)");
    delay(5000);

    digitalWrite(RELAY_PIN, HIGH);  // terkunci lagi
    Serial.println("Solenoid Dikunci Kembali");

    digitalWrite(LED_HIJAU_PIN, LOW);
    digitalWrite(LED_MERAH_PIN, HIGH);
}

// ==========================================
// Aksi akses ditolak
// ==========================================
void tolakAkses() {
    tone(BUZZER_PIN, 500);

    // Kedip merah selama 5 detik
    for (int i = 0; i < 10; i++) {
        digitalWrite(LED_MERAH_PIN, LOW);
        delay(250);
        digitalWrite(LED_MERAH_PIN, HIGH);
        delay(250);
    }

    noTone(BUZZER_PIN);
}