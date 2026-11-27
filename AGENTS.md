# AGENTS.md — ExaSatrya (SMKN 1 Tirtamulya)

Panduan kerja AI agent untuk repositori `Exam` (workspace root). Selaraskan juga dengan aturan lokal di `.agents/rules/AGENTS.md` (catatan: folder `.agents/` masuk `.gitignore`).

## 1. Aturan Kerja Agent

- **Baca dulu, grep semua caller**: Sebelum mengedit file apa pun, baca file terkait dan cari seluruh pemanggil/referensi (`Select-String` / `grep`) untuk memahami alur end-to-end. Jangan pernah menebak atau mengedit secara spekulatif.
- **Ikuti konvensi repo & hindari bloat**: Gunakan helper, middleware, dan pola yang sudah ada (`App\Helpers\SimpleSpreadsheetReader`, `App\Helpers\ImportPreview`, `PhpOffice\PhpSpreadsheet`). Hindari duplikasi logika, wrapper yang tidak perlu, dependensi baru, atau fitur di luar permintaan.
- **Perbaiki akar masalah**: Saat memperbaiki bug, perbaiki sekali di titik sumber bersama, bukan menambal gejala di masing-masing pemanggil.
- **Verifikasi wajib setelah edit terakhir**: Jalankan perintah validasi di Bagian 3 sesuai area yang diubah setelah edit terakhir selesai.
- **Tanpa klaim kosong**: Hanya sebutkan path file, nama simbol, perilaku kode, dan hasil perintah/tes yang benar-benar terlihat di output tool. Jangan mengarang angka atau mengklaim semua tes lulus bila ada yang gagal/dilewati.
- **Format penutup wajib**: Akhiri setiap respons dengan 2 baris singkat:
  - `Skipped:` langkah/cek yang dilewati atau belum dijalankan.
  - `Risk:` risiko, efek samping, atau hal yang perlu diperhatikan.

## 2. Ringkasan Stack & Struktur Repo

- **`exambro-backend/`** (Backend & Portal Web):
  - PHP `^8.3`, Laravel Framework `^13.8`, `maatwebsite/excel` `^4.0` (`PhpOffice\PhpSpreadsheet`), `laravel/tinker` `^3.0`.
  - Dev/Test/Lint: `phpunit/phpunit` `^12.5.12` (suite `tests/Unit` & `tests/Feature`, default testing SQLite `:memory:` via `phpunit.xml`), `laravel/pint` `^1.27`, `laravel/breeze`, `mockery/mockery` `^1.6`.
  - Frontend build: Vite `^8.0.0`, Tailwind CSS `^4.0.0` (`@tailwindcss/vite`), `laravel-vite-plugin` `^3.1`.
  - Route utama: `/portal` (portal ujian web utama siswa + `/ujian/{exam}` via `WebExamController`), `/download/apk` (redirect ke `/portal`), `/seb/exam/{exam}` (`SebExamController`), `/admin/*`, serta API `/api/login`, `/api/student/exams/*`, `/api/exams/{id}/start`, `/api/exams/{id}/finish`, `/api/violations`.
  - Config ujian (`config/exam.php`): koordinat geofence (`school_lat`, `school_lng`, `max_radius` = `600` m).
- **`exambro_mobile/`** *(telah dihapus — ujian siswa kini menggunakan Web Portal `/portal` tanpa aplikasi Android/APK)*
- **`deploy/` & `.github/workflows/deploy.yml`** (CI/CD & VPS):
  - Auto-deploy pada push ke branch `main` via SSH (`appleboy/ssh-action@v1.0.3`) ke `/var/www/exam_SKN1Tirtamulya`.

## 3. Perintah Validasi Cepat (Wajib Setelah Edit Kode)

Catatan shell Windows: gunakan PowerShell `;` sebagai pemisah perintah (bukan `&&`), dan sesuaikan dengan file yang diubah:

- **Backend (`exambro-backend/`)**:
  - Cek sintaks PHP file yang diubah: `php -l <path/to/file.php>`
  - Cek Blade & route setelah ubah view/route: `php artisan view:cache; php artisan route:list`
  - Jalankan unit/feature test: `php artisan test` (atau filter test terkait sebelum full run)
  - Format/lint PHP bila perlu: `.\vendor\bin\pint --test`
  - Jika ubah CSS/JS/Vite: `npm run build`

## 4. Area Sensitif (Jangan Diubah Sembarangan)

- **Autentikasi, Otorisasi & Keamanan Ujian**:
  - `AdminAuthController` (CAPTCHA & `RateLimiter` brute-force), middleware `EnsureUserRole`, isolasi peran `admin` / PIC Jurusan (`users.department`, `User::departmentClassIds()`) / `guru` / `siswa`.
  - API token & device lock (`Api\AuthController` dengan `sha256` `api_token` + guard `auth:api`, `Api\ApiExamController` single-device binding, violation lock, validasi GPS geofence dari `config/exam.php`).
  - Akses ujian web & SEB (`SebExamController`, `WebExamController`, kode masuk per-ujian `exams.access_code`).
- **Database, Migrasi & Seeder**:
  - Jangan mengubah migrasi lama yang sudah berjalan di produksi; buat migrasi baru yang aman & additive.
  - Hati-hati terhadap seeder (`DepartmentPicSeeder`) dan file database lokal (`*.sqlite` / `*.sqlite3` di-gitignore) agar tidak menimpa data/akun produksi.
- **Deploy, Secret & Signing Rilis**:
  - `.github/workflows/deploy.yml` dan `deploy/setup-vps.sh`: setiap push ke `main` langsung menjalankan `git reset --hard HEAD`, `git pull origin main`, `composer install --no-dev`, dan `php artisan migrate --force` di server produksi. Jangan ubah workflow/script deploy tanpa izin eksplisit.
  - File rahasia & konfigurasi lokal di-gitignore (`exambro-backend/.env`, `*.jks`, `*.keystore`): jangan pernah di-commit atau dibocorkan.

<!-- updated for web-based exam migration -->
