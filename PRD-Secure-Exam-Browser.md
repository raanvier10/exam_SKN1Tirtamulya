# PRD — Secure Exam Browser (Exambro)

## 1. Informasi Project

| Item | Detail |
|---|---|
| Project Name | Secure Exam Browser (Exambro) |
| Platform | Web Admin + Mobile Android |
| Admin | Web Application |
| Siswa | Flutter Mobile Application |
| Backend | Laravel REST API |
| Database | MySQL |
| Exam Engine | Google Forms melalui Secure WebView |
| Target | Sekolah/Kampus/Lembaga Pendidikan |
| Status | MVP |

## 2. Overview

Secure Exam Browser (Exambro) adalah aplikasi mobile yang digunakan siswa untuk mengakses ujian berbasis Google Form melalui lingkungan ujian yang lebih aman.

Aplikasi tidak bertujuan menggantikan Google Forms sebagai sistem pembuatan dan penyimpanan soal. Google Forms tetap digunakan sebagai exam engine, sedangkan aplikasi berfungsi sebagai secure gateway/wrapper yang mengatur autentikasi, jadwal, countdown, session, serta pembatasan penggunaan perangkat selama ujian.

Admin mengelola siswa dan jadwal ujian melalui Web Admin. Admin cukup memasukkan link Google Form, menentukan jadwal, durasi, peserta, dan aturan keamanan.

Siswa hanya dapat melihat ujian yang tersedia untuk dirinya dan pada waktu yang telah ditentukan.

## 3. Problem Statement

Penggunaan Google Forms untuk ujian memiliki beberapa keterbatasan keamanan.

Siswa dapat:

- Membuka Google Form langsung melalui browser.
- Menyalin URL Google Form.
- Berpindah ke aplikasi lain.
- Menggunakan Google Search/Google Lens.
- Menggunakan split screen.
- Melakukan screenshot.
- Menggunakan floating window.
- Keluar dari halaman ujian tanpa monitoring yang jelas.

Selain itu, admin harus membagikan link Google Form secara manual kepada peserta.

Oleh karena itu diperlukan sebuah aplikasi yang menjadi lapisan keamanan di atas Google Forms, tanpa perlu membangun sistem ujian dan penyimpanan jawaban sendiri.

## 4. Goals

### Primary Goals

1. Membatasi akses Google Form hanya melalui aplikasi.
2. Menyediakan jadwal ujian kepada siswa.
3. Menampilkan countdown sebelum ujian dimulai.
4. Membuat secure exam environment.
5. Mendeteksi aktivitas yang dianggap sebagai pelanggaran.
6. Mengurangi kemungkinan siswa membuka aplikasi lain selama ujian.
7. Memungkinkan admin mengelola siswa dan ujian dengan mudah.
8. Tetap menggunakan Google Forms sebagai tempat pengerjaan dan penyimpanan jawaban.

### Secondary Goals

1. Mengurangi kebutuhan server.
2. Mengurangi kompleksitas backend.
3. Memudahkan sekolah/kampus menggunakan sistem yang sudah familiar.
4. Meminimalkan biaya infrastruktur.

## 5. Non-Goals

Aplikasi tidak akan:

- Membuat bank soal sendiri pada MVP.
- Menyimpan soal di database aplikasi.
- Menyimpan jawaban siswa di database aplikasi.
- Menggantikan Google Forms.
- Membuat sistem penilaian sendiri.
- Membuat LMS lengkap.
- Membuat video proctoring pada MVP.
- Menggunakan AI untuk mendeteksi kecurangan pada MVP.

## 6. User / Role

Sistem hanya memiliki 2 role utama.

### A. Admin

Admin menggunakan Web Admin.

Tanggung jawab:

- Mengelola siswa.
- Membuat ujian.
- Mengatur jadwal.
- Memasukkan Google Form.
- Mengatur peserta.
- Mengatur keamanan.
- Melihat aktivitas dan pelanggaran.

### B. Siswa

Siswa menggunakan aplikasi Android.

Tanggung jawab:

- Login.
- Melihat ujian.
- Menunggu jadwal ujian.
- Melakukan security check.
- Mengerjakan ujian.
- Submit Google Form.

## 7. Platform

### Admin — Web Application

Web digunakan karena admin membutuhkan layar yang lebih besar untuk:

- Mengelola banyak siswa.
- Membuat jadwal.
- Memasukkan link.
- Melihat monitoring.
- Melihat log.
- Mengelola data.

### Siswa — Flutter Android

Flutter digunakan karena membutuhkan akses ke fitur sistem Android yang lebih dalam untuk:

- Secure screen.
- Screen capture protection.
- App lifecycle detection.
- Kiosk/Lock Task.
- Device information.
- Overlay detection.
- Multi-window detection.

## 8. Core Features

### 8.1 Authentication

**Admin**

- Login
- Logout
- Session management
- Change password

**Siswa**

- Login menggunakan username/NIS + password
- Logout
- Session management
- Device binding

## 9. Admin Features

### 9.1 Dashboard

Dashboard menampilkan:

- Total siswa
- Total ujian
- Ujian hari ini
- Ujian aktif
- Ujian selesai
- Total pelanggaran

Contoh:

```
TOTAL SISWA       350
UJIAN HARI INI     4
UJIAN AKTIF        1
PELANGGARAN       12
```

### 9.2 Student Management

Admin dapat:

- Menambah siswa
- Mengubah data siswa
- Menghapus siswa
- Mengaktifkan/nonaktifkan akun
- Reset password
- Melihat device yang terdaftar

Data siswa:

- NIS
- Nama
- Username
- Password
- Kelas
- Status
- Device ID
- Created At

## 10. Exam Management

Admin dapat membuat ujian.

Data:

- Nama Ujian
- Deskripsi
- Google Form URL
- Tanggal
- Jam Mulai
- Jam Selesai
- Durasi
- Peserta
- Status
- Security Level

Contoh:

```
UAS Basis Data

14 Agustus 2026
08.00 - 10.00 WIB

Durasi: 120 Menit
Security: STRICT
```

## 11. Google Form Integration

Admin memasukkan:

- Google Form URL

URL disimpan di backend. Siswa tidak melihat URL asli pada UI.

Siswa hanya melihat:

```
UAS BASIS DATA

14 Agustus 2026
08.00 - 10.00

[ MULAI UJIAN ]
```

Ketika siswa menekan tombol mulai:

```
Mobile App
    ↓
Backend
    ↓
Validate Session
    ↓
Validate Schedule
    ↓
Create Exam Session
    ↓
Secure WebView
    ↓
Google Form
```

## 12. Student Dashboard

Setelah login, siswa langsung melihat:

```
Ujian Hari Ini
┌─────────────────────────┐
│ UAS BASIS DATA          │
│                         │
│ 14 Agustus 2026         │
│ 08.00 - 10.00 WIB       │
│                         │
│ Durasi 120 menit        │
│                         │
│ [ MULAI UJIAN ]         │
└─────────────────────────┘
```

Siswa tidak perlu mencari link Google Form.

## 13. Exam Countdown

Jika ujian belum dimulai:

```
UJIAN BELUM DIMULAI

UAS BASIS DATA

Mulai dalam:
00 : 25 : 43
```

Button: `[ BELUM TERSEDIA ]`

Ketika waktu mulai tiba:

```
UJIAN SUDAH DIMULAI

[ MULAI UJIAN ]
```

## 14. Security Check

Sebelum masuk ujian, aplikasi melakukan pengecekan:

```
SECURITY CHECK

✓ Internet Connection
✓ Secure Screen
✓ Screen Capture Protection
✓ App Environment
✓ Device Status
✓ Exam Session

DEVICE READY

[ MULAI UJIAN ]
```

Jika ada masalah:

```
⚠️ PERANGKAT TIDAK MEMENUHI
PERSYARATAN UJIAN

Split Screen terdeteksi.

Silakan tutup aplikasi lain.
```

## 15. Secure Exam Mode

Ketika ujian dimulai, aplikasi masuk ke Secure Exam Mode.

### Security Features

**Screen Protection**
- Anti Screenshot
- Anti Screen Recording
- Secure Screen

**Navigation Protection**
- Disable Back
- Disable external navigation
- Prevent browser opening

**App Switching**
- Detect Home
- Detect Recent Apps
- Detect App Switching
- Violation counter

**Multitasking**
- Anti Split Screen
- Anti Multi Window
- Floating Window Detection

**Input Protection**
- Disable Copy
- Disable Paste
- Disable Cut
- Disable Text Selection
- Disable Long Press

## 16. Violation System

Setiap tindakan mencurigakan dicatat.

Contoh violation:

- `APP_SWITCH`
- `SCREENSHOT_ATTEMPT`
- `SPLIT_SCREEN`
- `FLOATING_WINDOW`
- `EXIT_EXAM`

Admin dapat menentukan Maximum Violation (contoh: 3).

**Pelanggaran 1**
```
⚠️ PERINGATAN

Anda meninggalkan aplikasi ujian.

Pelanggaran: 1 / 3
```

**Pelanggaran 2**
```
⚠️ PERINGATAN TERAKHIR

Pelanggaran: 2 / 3
```

**Pelanggaran 3**
```
🔒 UJIAN DIKUNCI

Batas pelanggaran tercapai.

Hubungi pengawas.
```

## 17. Exam Session

Setiap siswa mendapatkan Exam Session.

Data:

- `session_id`
- `user_id`
- `exam_id`
- `device_id`
- `started_at`
- `expired_at`
- `status`

Status:

- `WAITING`
- `ACTIVE`
- `PAUSED`
- `COMPLETED`
- `LOCKED`
- `EXPIRED`

Session digunakan agar sistem dapat mengetahui apakah siswa benar-benar sedang menjalankan ujian.

## 18. Device Binding

Pada saat pertama kali menggunakan aplikasi:

```
Account
   +
Device
   ↓
Registered
```

Admin dapat melihat:

```
Dimas
Device: Android XYZ
Status: Registered
```

Tujuannya untuk mengurangi kemungkinan satu akun digunakan di banyak perangkat.

## 19. Exam Completion

Setelah siswa selesai mengerjakan Google Form:

```
UJIAN SELESAI

Jawaban telah dikirim melalui
Google Forms.

Terima kasih.

[ KEMBALI ]
```

Backend mencatat:

- `finished_at`
- `status = COMPLETED`

Aplikasi tidak perlu menyimpan jawaban.

## 20. Monitoring

Admin dapat melihat:

| Siswa | Status | Pelanggaran |
|---|---|---|
| Andi | Active | 0 |
| Budi | Active | 1 |
| Citra | Locked | 3 |
| Deni | Completed | 0 |

Status:

- 🟢 Active
- 🟡 Warning
- 🔴 Locked
- 🔵 Completed

## 21. Security Level

Admin dapat memilih level keamanan.

**BASIC**
- Anti screenshot
- Anti copy
- Disable back
- Full screen

**STANDARD** (Basic +)
- App switching detection
- Violation counter
- Anti split screen
- Secure WebView

**STRICT** (Standard +)
- Kiosk Mode
- Device binding
- Floating window detection
- Developer options detection
- Root detection
- Emulator detection
- Force submit/lock
- Advanced logging

## 22. Database Structure

MVP dapat menggunakan 6 tabel utama.

**users**
- id
- name
- username
- password
- role
- class_id
- status
- created_at
- updated_at

**classes**
- id
- name
- description
- created_at
- updated_at

**exams**
- id
- title
- description
- google_form_url
- start_at
- end_at
- duration
- security_level
- max_violation
- status
- created_at
- updated_at

**exam_participants**
- id
- exam_id
- user_id
- status
- started_at
- finished_at

**exam_sessions**
- id
- exam_id
- user_id
- device_id
- session_token
- started_at
- expired_at
- status

**violations**
- id
- exam_id
- user_id
- session_id
- type
- description
- created_at

## 23. API Architecture

```
Flutter App
     │
     │ REST API
     ▼
Laravel Backend
     │
     ├── Authentication
     ├── User
     ├── Exam
     ├── Schedule
     ├── Session
     ├── Violation
     └── Device
             │
             ▼
          MySQL
```

Google Form:

```
Flutter
   │
   ▼
Secure WebView
   │
   ▼
Google Forms
```

## 24. API Endpoint MVP

**Authentication**
```
POST /api/login
POST /api/logout
GET  /api/me
```

**Student**
```
GET /api/student/exams/today
GET /api/student/exams/{id}
```

**Exam**
```
POST /api/exams/{id}/start
POST /api/exams/{id}/finish
```

**Session**
```
POST /api/exam-session
GET  /api/exam-session/{id}
```

**Violation**
```
POST /api/violations
GET  /api/exams/{id}/violations
```

## 25. User Flow

### Admin

```
Login
 ↓
Dashboard
 ↓
Buat Siswa
 ↓
Buat Ujian
 ↓
Masukkan Google Form
 ↓
Atur Jadwal
 ↓
Pilih Peserta
 ↓
Atur Security
 ↓
Publish
```

### Siswa

```
Login
 ↓
Dashboard
 ↓
Ujian Hari Ini
 ↓
Countdown
 ↓
Security Check
 ↓
Start Exam
 ↓
Secure Exam Mode
 ↓
Google Form
 ↓
Submit
 ↓
Exam Completed
```

## 26. MVP Scope

### WAJIB

**Admin**
- [ ] Login
- [ ] Dashboard
- [ ] CRUD siswa
- [ ] CRUD ujian
- [ ] Input Google Form
- [ ] Jadwal ujian
- [ ] Pilih peserta
- [ ] Atur durasi
- [ ] Atur violation limit

**Siswa**
- [ ] Login
- [ ] Dashboard
- [ ] Ujian hari ini
- [ ] Countdown
- [ ] Security check
- [ ] Secure WebView
- [ ] Google Form
- [ ] Exam session

**Security**
- [ ] Anti screenshot
- [ ] Anti screen recording
- [ ] Disable back
- [ ] Anti copy/paste
- [ ] Anti text selection
- [ ] App switching detection
- [ ] Violation counter
- [ ] Anti split screen
- [ ] Full screen

## 27. Future Development

Setelah MVP stabil, baru tambahkan:

- Face verification
- Camera monitoring
- Audio monitoring
- AI proctoring
- Device integrity
- Root detection
- Emulator detection
- Advanced anti-tampering
- Realtime monitoring
- Notification system
- Import siswa Excel
- Import peserta massal
- Export laporan
- Multi-school management

## 28. Prinsip Utama Sistem

> "Don't build another Google Forms. Build a secure gateway to Google Forms."

Jadi fokus development-nya bukan membuat fitur soal sebanyak-banyaknya, tetapi membuat experience sebelum, selama, dan setelah ujian menjadi aman dan terkontrol.

**Stack final yang direkomendasikan:**

```
ADMIN
Laravel + Tailwind
       │
       ▼
BACKEND
Laravel API
       │
       ▼
MySQL
       ▲
       │
FLUTTER ANDROID
       │
       ▼
SECURE WEBVIEW
       │
       ▼
GOOGLE FORMS
```

Stack ini paling cocok dengan tujuan awal: aplikasi ringan, server murah, tidak perlu membuat sistem soal sendiri, namun tetap memiliki layer keamanan yang jauh lebih kuat dibandingkan sekadar membagikan link Google Form.
