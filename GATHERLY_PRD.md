# GATHERLY — Product Requirements Document (PRD)

**Versi:** 1.1 (revisi dari draft 1.0)
**Status:** Siap dijadikan panduan pengembangan
**Tipe produk:** Event Management & Free Digital Ticketing (Web, responsive)
**Konteks:** Project portfolio, dikerjakan oleh 1 developer

> **Cara membaca dokumen ini.** Setiap keputusan penting diberi tag **[Keputusan]** beserta alasannya. Bagian yang saya ubah dari draft awal diberi tag **[Perubahan dari draft]** supaya mudah ditelusuri.

---

## Daftar Isi

1. Ringkasan Produk
2. Keputusan Utama & Perubahan dari Draft
3. Target Pengguna & Peran
4. Scope MVP
5. Prioritas Fitur (Must / Should / Nice to Have)
6. User Flow
7. Functional Requirements
8. Business Rules & State Machine
9. Technical Requirements
10. Model Data
11. Rute & Endpoint Utama
12. Non-Functional Requirements
13. Strategi Testing
14. Roadmap Pengembangan
15. Acceptance Criteria MVP
16. Risiko & Mitigasi
17. Open Items

---

## 1. Ringkasan Produk

### 1.1 Deskripsi
Gatherly membantu organizer mengelola event gratis dari awal sampai akhir dalam satu tempat: membuat dan mempublikasikan event, mengatur tiket dan kuota, menerima registrasi, menerbitkan tiket digital ber-QR, melakukan check-in, dan melihat laporan kehadiran.

### 1.2 Masalah yang diselesaikan
| Masalah | Solusi Gatherly |
|---|---|
| Data registrasi tersebar (form, spreadsheet, chat) | Satu sistem registrasi dengan daftar peserta terpusat |
| Kuota dikelola manual, rawan kelebihan | Alokasi kuota atomik di database |
| Tiket tidak terdistribusi rapi | Tiket digital otomatis, dikirim via email dan tersedia di akun |
| Check-in lambat dan manual | Scan QR dari browser + pencarian manual sebagai cadangan |
| Tiket bisa dipakai berulang | Status tiket satu arah + update atomik, check-in hanya sekali |
| Tidak ada ringkasan evaluasi | Dashboard registrasi dan kehadiran + ekspor CSV |

### 1.3 Nilai portfolio
Gatherly dipilih karena memuat masalah teknis yang nyata dan bisa didemonstrasikan: **concurrency** (kuota dan check-in), **role-based authorization**, **transaksi database**, **antrean email**, dan **QR validation**. Inilah yang sebaiknya menjadi fokus kualitas, bukan jumlah fitur.

### 1.4 Prinsip produk
1. **Satu alur inti yang solid** (buat event → daftar → tiket → check-in → laporan) lebih bernilai daripada banyak fitur setengah jadi.
2. **Integritas data di atas segalanya**: tidak boleh ada tiket melebihi kuota dan tidak boleh ada check-in ganda.
3. **Sederhana dulu**: pilih solusi paling sedikit komponennya selama memenuhi kebutuhan.

---

## 2. Keputusan Utama & Perubahan dari Draft

### 2.1 Keputusan hasil diskusi
| # | Topik | Keputusan | Alasan |
|---|---|---|---|
| D1 | Arsitektur | **Laravel + Inertia.js (React)**, satu repo monolit | Tidak perlu membuat API terpisah, CORS, dan token auth. Auth session + CSRF bawaan Laravel sudah aman. Satu deploy, bug lebih sedikit. React + Tailwind tetap dipakai sesuai draft |
| D2 | Akun peserta | **Peserta wajib punya akun** untuk mendaftar event (alasan lengkap di 2.2) | Menyederhanakan otorisasi tiket, duplikasi, halaman My Tickets, dan pembatalan |
| D3 | Platform Admin | **Sesuai draft** (daftar user, organizer, event, kategori; dashboard dan nonaktifkan sebagai P1) | Dipertahankan atas permintaan Anda. Dijadwalkan di akhir roadmap supaya tidak mengganggu alur inti (lihat risiko R1) |
| D4 | Event Staff | **Organizer menambahkan staff lewat email akun yang sudah terdaftar** | Tidak perlu sistem undangan atau token. Staff cukup mendaftar akun biasa dulu |

### 2.2 Rekomendasi: kenapa peserta wajib akun?
Anda masih bingung soal ini, jadi berikut perbandingannya.

| Aspek | Wajib akun | Guest (tanpa akun) |
|---|---|---|
| Halaman My Tickets | Natural (daftar tiket milik user) | Tidak ada, harus lewat link email |
| Akses ulang tiket | Login | Hanya dari email; jika email hilang, tiket hilang |
| Otorisasi "tiket milik sendiri" | Cek `user_id` sederhana | Perlu token publik per tiket (permukaan serangan baru) |
| Cegah registrasi ganda | `unique(event_id, user_id)` | Harus bergantung pada email yang bisa dipalsukan |
| Pembatalan registrasi | Login lalu klik batal | Perlu link bertoken |
| Friksi peserta | Sedikit lebih tinggi | Rendah |

**Rekomendasi: wajib akun.** Draft Anda sendiri sudah mengandalkan "My Tickets", "tiket diakses lewat akun peserta", dan "peserta melihat registrasi miliknya", yang semuanya mensyaratkan akun. Mengurangi friksi dilakukan dengan cara lain: form registrasi akan **terisi otomatis dari profil**, dan jika user belum login, ia diarahkan ke login/daftar lalu **dikembalikan ke halaman event yang sama** (intended redirect).
Guest registration dicatat sebagai pengembangan lanjutan (bagian Nice to Have).

### 2.3 Perubahan dari draft (inkonsistensi yang saya rapikan)
| # | Di draft | Masalah | Perubahan |
|---|---|---|---|
| C1 | Pembatalan registrasi P1 di 5.4, tetapi masuk Phase 2 di roadmap | Kontradiktif | Dijadikan **Should Have** (dikerjakan hanya jika waktu cukup, bukan syarat MVP) |
| C2 | Staff management P1, tetapi check-in oleh staff P0 | Staff tidak bisa check-in tanpa ditugaskan | STF-01, STF-02 dinaikkan ke **Must Have** (versi sederhana). STF-05 tetap Should |
| C3 | Status registrasi punya "Checked-in" dan tiket juga punya "Checked-in" | Dua sumber kebenaran, rawan tidak konsisten | Kehadiran disimpan **hanya di tiket + tabel check-in**. Status registrasi hanya **Registered / Cancelled** |
| C4 | Status tiket punya Cancelled dan Revoked | Perbedaannya tidak terdefinisi untuk MVP | MVP hanya **Active / Checked-in / Cancelled**. Revoked ditunda |
| C5 | Kapasitas event (EVT-09) dan kuota tiket sama-sama ada | Dua angka bisa bertentangan | **Kapasitas event = jumlah kuota semua ticket type** (dihitung, bukan input terpisah). Satu sumber kebenaran |
| C6 | Status "Completed" tanpa mekanisme | Perlu scheduler atau job | **Completed dihitung dari waktu** (`end_at` sudah lewat), bukan disimpan dan tanpa cron |
| C7 | "Event valid untuk attendance" tidak terdefinisi | Aturan check-in ambigu | Check-in diizinkan jika event **Published** dan waktu sekarang berada di **jendela check-in** (mulai 2 jam sebelum `start_at` sampai `end_at`). Angka 2 jam adalah konstanta yang mudah diubah |
| C8 | Verifikasi email tidak disebut | Akun bisa dibuat dengan email milik orang lain | **Should Have**: email verification Laravel bawaan |
| C9 | Chart.js | Cocok, tetapi di React lebih mudah lewat wrapper | Gunakan **react-chartjs-2** |
| C10 | Hosting di Render | Disk Render bersifat sementara, sehingga poster upload hilang saat redeploy | Poster disimpan di **Supabase Storage** (satu platform dengan database) |

---

## 3. Target Pengguna & Peran

### 3.1 Model peran **[Keputusan]**
- Kolom `users.role` dengan nilai: `admin`, `organizer`, `attendee`.
- **Event Staff bukan peran global**, melainkan **penugasan per event** (tabel `event_staff`). Siapa pun yang punya akun dapat ditugaskan sebagai staff pada event tertentu.
- Otorisasi memakai **Laravel Policies + Gates**, tanpa package tambahan (mis. Spatie Permission).

*Alasan:* kebutuhan hak akses Gatherly kecil dan statis. Package permission menambah kompleksitas tanpa manfaat di skala MVP.

### 3.2 Matriks akses
| Kemampuan | Admin | Organizer | Staff (event ditugaskan) | Attendee |
|---|---|---|---|---|
| Lihat event publik | ✓ | ✓ | ✓ | ✓ |
| Daftar event & lihat tiket sendiri | ✓ | ✓ | ✓ | ✓ |
| Buat/kelola event sendiri | – | ✓ | – | – |
| Kelola ticket types event sendiri | – | ✓ | – | – |
| Lihat peserta & ekspor CSV event sendiri | – | ✓ | – | – |
| Tambah/hapus staff event sendiri | – | ✓ | – | – |
| Lihat analytics event sendiri | – | ✓ | – | – |
| Check-in (scan/manual) | – | ✓ (event sendiri) | ✓ | – |
| Lihat daftar user/organizer/event semua | ✓ | – | – | – |
| Kelola kategori | ✓ | – | – | – |
| Nonaktifkan event/user | ✓ | – | – | – |

> **Cara menjadi organizer:** saat registrasi, user memilih "Saya penyelenggara event". Tidak ada proses approval pada MVP (admin dapat menonaktifkan akun bila perlu).

---

## 4. Scope MVP

### 4.1 Termasuk (In Scope)
Authentication, profil, RBAC, event management (draft/publish/cancel), kategori, halaman publik (daftar, cari, filter, detail), ticket types + kuota, registrasi, tiket digital ber-QR, email konfirmasi, daftar peserta, check-in QR + manual, staff assignment sederhana, dashboard dasar, ekspor CSV, panel admin, UI responsive.

### 4.2 Tidak termasuk (Out of Scope)
Pembayaran/tiket berbayar, refund, kupon, seat map, livestream, aplikasi native, multi-currency, sertifikat, sponsor, marketing automation, custom domain, subscription, waitlist, **custom registration field**, **offline check-in**, **guest registration**, **multi-organization**, **ticket transfer**.

### 4.3 Alternatif yang lebih sederhana untuk ide yang berat
| Ide di draft | Kenapa berat untuk solo dev | Alternatif untuk MVP |
|---|---|---|
| Event online/offline/hybrid (EVT-13) | Tiap format butuh field berbeda (link, venue) | Satu kolom `format` + field `location` dan `online_url` opsional. Tanpa logika khusus |
| Notifikasi update/cancel massal | Banyak email sekaligus | Pakai queue database, dikirim per batch. Cancel event tetap Must, update event Should |
| Attendance history lengkap (CHK-12) | Butuh UI log terpisah | Cukup daftar "recent check-ins" di dashboard event (data sudah ada di tabel check-in) |
| Audit trail platform (ADM-08) | Butuh pencatatan di banyak titik | Tampilkan **event dan user terbaru** dari data yang sudah ada, tanpa tabel audit khusus |
| Scan QR native | Butuh app mobile | Scan dengan **kamera browser** (library `html5-qrcode`) + pencarian manual sebagai cadangan |

---

## 5. Prioritas Fitur

> Pemetaan: **Must Have** = syarat MVP selesai · **Should Have** = penting, dikerjakan setelah semua Must selesai (masih dalam rencana MVP bila waktu ada, atau segera setelahnya) · **Nice to Have** = pengembangan jangka panjang.

### 5.1 Must Have (wajib MVP)
| Area | Fitur | ID |
|---|---|---|
| Auth | Daftar, login/logout, reset password, role-based access | AUTH-01,02,03,05,07 |
| Event | Buat, edit, draft, publish, batalkan, field dasar, tanggal, periode registrasi, status | EVT-01 s/d 08, 10, 11 |
| Tiket | Ticket type, nama/deskripsi, kuota, periode ketersediaan, inventory, ID unik, QR, anti-oversell, status | TKT-01 s/d 10 |
| Registrasi | Daftar, pilih tipe, validasi, validasi kuota, nomor registrasi, konfirmasi, daftar peserta organizer, ekspor CSV | REG-01 s/d 09, 11 |
| Tiket digital | Halaman tiket, info event/peserta/tipe, QR, akses dari akun, kirim email, tiket batal tidak bisa dipakai | DT-01 s/d 07, 09 |
| Check-in | Scanner QR, validasi, deteksi tiket terpakai, timestamp, staff ID, cari manual, manual check-in, anti-duplikat, statistik | CHK-01 s/d 11 |
| Staff | Tambah/hapus staff (via email), akses terbatas | STF-01,02,03,04 |
| Dashboard | Total event, registrasi, tiket, check-in, attendance rate, data per event | ANA-01 s/d 05, 08 |
| Notifikasi | Email registrasi berhasil + tiket | NOT-01,02 |
| Admin | Daftar user, organizer, event; kelola kategori | ADM-02,03,04,05 |

### 5.2 Should Have (penting, boleh setelah semua Must)
- Profil user dan profil organisasi organizer (AUTH-04, AUTH-06)
- Pencarian & filter event publik (EVT-12), format event (EVT-13)
- Pencarian/filter peserta (REG-10)
- Pembatalan registrasi oleh peserta (REG-12) dan pembebasan kuota
- Unduh/cetak tiket (DT-08)
- Attendance history (CHK-12) dan aktivitas check-in staff (STF-05)
- Chart registration trend & ticket distribution (ANA-06, 07)
- Email notifikasi pembatalan/pembaruan event (NOT-03, 04) dan antrean email (NOT-05)
- Email verification (C8)
- Dashboard admin, nonaktifkan event/user, aktivitas platform terbatas (ADM-01, 06, 07, 08)

### 5.3 Nice to Have (jangka panjang)
Custom registration fields, waitlist, email reminder, sertifikat, duplikasi event, analytics lanjutan, guest registration, offline check-in (PWA), tiket berbayar + payment gateway, ticket transfer, multi-organization, SaaS (subscription, custom branding/domain, webhook, API).

---

## 6. User Flow

### 6.1 Organizer: membuat dan mempublikasikan event
1. Login → Dashboard organizer.
2. **Create Event** → isi nama, deskripsi, poster, kategori, lokasi, `start_at`, `end_at`, periode registrasi.
3. Tambah ticket type (nama, deskripsi, kuota, periode tersedia).
4. **Simpan Draft** (validasi ringan).
5. **Preview** halaman publik.
6. **Publish** → validasi lengkap (data wajib, minimal 1 ticket type dengan kuota > 0, tanggal valid). Gagal → tampilkan daftar kekurangan.
7. Event tampil di halaman publik.

### 6.2 Attendee: mendaftar dan mendapat tiket
1. Buka halaman publik → cari/telusuri event → detail event.
2. Pilih ticket type → klik Daftar.
3. Jika belum login → login/daftar → **kembali ke halaman event yang sama**.
4. Form registrasi terisi dari profil → konfirmasi.
5. Sistem (dalam satu transaksi DB): validasi event/periode/kuota/duplikat → buat registrasi + nomor → terbitkan tiket + token QR.
6. Halaman sukses menampilkan tiket. Email konfirmasi + tiket masuk antrean.
7. Tiket tersedia di **My Tickets**.

### 6.3 Staff: check-in
1. Login → daftar event yang ditugaskan → buka halaman check-in.
2. Scan QR dengan kamera.
3. Sistem memvalidasi: token ada → tiket milik event ini → status Active → event Published dan dalam jendela check-in.
4. Valid → ubah status secara atomik, catat `checked_in_at` + `checked_in_by`, tampilkan **hijau** (nama, tipe tiket).
5. Tidak valid → tampilkan **merah** dengan alasan spesifik (tidak ditemukan / event berbeda / sudah dipakai pada pukul X oleh Y / dibatalkan / di luar jendela check-in).
6. Cadangan: cari peserta berdasarkan nama/email/nomor registrasi → manual check-in (aturan validasi identik).

### 6.4 Organizer: membatalkan event
1. Buka event → Cancel Event → dialog konfirmasi.
2. Status → Cancelled. Registrasi baru ditutup. Semua tiket Active → Cancelled (dalam satu transaksi).
3. Email pembatalan diantrekan ke semua peserta terdaftar.

### 6.5 Organizer: menambah staff
1. Buka event → tab Staff → masukkan email.
2. Jika akun dengan email tsb ada → penugasan dibuat. Jika tidak ada → pesan "Pengguna belum terdaftar, minta mendaftar dahulu".
3. Hapus penugasan kapan saja.

---

## 7. Functional Requirements (Rincian)

Berikut rincian perilaku di luar tabel ID pada draft. ID mengikuti draft agar tetap bisa ditelusuri.

### 7.1 Authentication & User
- Registrasi: nama, email (unik), password (min. 8 karakter), pilihan "penyelenggara event".
- Login/logout, reset password via email (bawaan Laravel).
- Rate limiting pada login dan reset password.
- *(Should)* Email verification, edit profil, profil organisasi (nama, deskripsi, logo).

### 7.2 Event Management
- Field: `title`, `slug` (unik, otomatis), `description`, `poster`, `category_id`, `format` (online/offline/hybrid), `location`, `online_url`, `start_at`, `end_at`, `registration_start_at`, `registration_end_at`, `status`.
- **Aturan edit:**
  - Draft: semua field dapat diubah.
  - Published: semua field dapat diubah **kecuali** kuota ticket type tidak boleh di bawah jumlah tiket yang sudah terbit, dan ticket type yang sudah punya tiket tidak dapat dihapus (hanya dinonaktifkan).
  - *(Should)* Perubahan `start_at`, `end_at`, atau lokasi memicu email pembaruan.
  - Cancelled/Completed: read-only.
- Upload poster: JPG/PNG/WebP, maks. 2 MB, disimpan di Supabase Storage.

### 7.3 Ticket Types & Kuota
- Field: `name`, `description`, `quota`, `available_from`, `available_until`, `is_active`.
- Inventory yang ditampilkan: kuota, terjual (issued), sisa.
- **1 registrasi = 1 tiket** (sesuai asumsi draft).
- Kapasitas event = jumlah kuota semua ticket type aktif (C5).

### 7.4 Registrasi
- Validasi (semuanya di backend): user login, event Published, registrasi dalam periode, ticket type aktif & dalam periode, kuota tersedia, belum punya registrasi aktif pada event ini.
- Data registrasi: nama dan email (diambil dari akun), nomor telepon (opsional).
- **Nomor registrasi** berformat `GTH-{YYMM}-{6 karakter acak}` (unik).
- Organizer dapat melihat daftar peserta (paginasi), mengekspor CSV (nomor registrasi, nama, email, ticket type, status registrasi, status tiket, waktu daftar, waktu check-in).
- *(Should)* Pembatalan oleh peserta jika event masih Published dan sebelum `start_at`: registrasi → Cancelled, tiket → Cancelled, kuota otomatis kembali karena dihitung dari tiket Active/Checked-in.

### 7.5 Tiket Digital
- Halaman tiket menampilkan: nama event, tanggal/lokasi, nama peserta, ticket type, nomor registrasi, QR, status tiket (badge jelas).
- QR berisi **hanya token acak** (`ticket_code`, ≥ 32 karakter, kriptografis acak). Tidak ada nama/email/ID berurutan.
- QR digambar sebagai SVG. Dihasilkan **sekali saat dibutuhkan** dari token, tidak disimpan sebagai file.
- Email: berisi ringkasan + tautan ke tiket (login diperlukan) + QR sebagai gambar inline.
- *(Should)* Tombol cetak (CSS print), tanpa pembuatan PDF di server.

### 7.6 Check-in
- Scanner: kamera belakang default, hasil ditampilkan besar dan berwarna, bunyi/getar opsional.
- Setelah scan, scanner **berhenti sejenak** (±1,5 detik) untuk mencegah scan ganda yang tidak disengaja.
- Pencarian manual: nama, email, nomor registrasi; paginasi; tombol Check-in per baris.
- Statistik real-time ringan: checked-in / total, diperbarui setelah setiap check-in (cukup memperbarui dari respons, tanpa WebSocket).

### 7.7 Dashboard
- **Organizer:** total event, event published, event mendatang, total registrasi, total tiket, total check-in, attendance rate, daftar event dengan status, aksi cepat.
- **Per event:** info event, jumlah registrasi, utilisasi kuota, distribusi tiket per tipe, check-in, attendance rate, 10 check-in terakhir, daftar peserta. *(Should)* grafik tren registrasi dan distribusi.
- **Attendance rate** = tiket Checked-in ÷ total tiket Active+Checked-in × 100%.
- Seluruh angka dihitung dengan `COUNT`/`GROUP BY` agregat, tanpa memuat koleksi ke memori.

### 7.8 Admin
- Daftar user (cari, filter peran), daftar organizer, daftar semua event (filter status).
- CRUD kategori (hapus hanya jika tidak dipakai event).
- *(Should)* Dashboard platform (total user, organizer, event, event published, registrasi, daftar terbaru), nonaktifkan event/user.
- **Nonaktifkan user:** kolom `is_active`; user nonaktif tidak bisa login. **Nonaktifkan event:** `is_disabled` oleh admin menyembunyikan event dari publik dan menutup registrasi.

---

## 8. Business Rules & State Machine

### 8.1 Event
```
Draft ──publish──▶ Published ──cancel──▶ Cancelled
  │                    │
  └────────────────────┴── (setelah end_at: dianggap Completed, dihitung dari waktu)
```
- Draft dapat dihapus selama belum pernah dipublikasikan.
- Hanya Draft yang dapat dipublikasikan. Published tidak kembali ke Draft jika sudah ada registrasi.
- Cancelled bersifat final.
- Organizer hanya dapat mengelola event miliknya (policy).

### 8.2 Registrasi
```
Registered ──cancel──▶ Cancelled
```
- Satu user maksimal satu registrasi **aktif** per event. Diterapkan lewat **partial unique index** PostgreSQL: `UNIQUE (event_id, user_id) WHERE status = 'registered'`. Setelah membatalkan, user boleh mendaftar lagi selama kuota ada.

### 8.3 Tiket
```
Active ──check-in──▶ Checked-in
Active ──cancel────▶ Cancelled
```
- Transisi lain **ditolak**. Checked-in dan Cancelled adalah status akhir.
- Status tiket dan registrasi harus konsisten: tiket Cancelled ⇔ registrasi Cancelled atau event Cancelled.

### 8.4 Aturan kritis
| Aturan | Implementasi |
|---|---|
| Registrasi + tiket atomik | Satu `DB::transaction` |
| Anti oversell | Dalam transaksi: `SELECT ... FOR UPDATE` pada baris `ticket_types` → hitung tiket aktif → bandingkan dengan kuota → insert |
| Anti check-in ganda | `UPDATE tickets SET status='checked_in', ... WHERE id=? AND status='active'`; jika *affected rows* = 0 berarti sudah dipakai/batal. Tidak ada pola "baca lalu tulis" |
| Manual = QR | Keduanya memanggil **satu service yang sama** (`CheckInService`) |
| Email gagal ≠ registrasi gagal | Email dikirim **setelah commit** melalui queue |
| Otorisasi di backend | Policy pada setiap controller action; query selalu di-scope (mis. `auth()->user()->events()`) |

---

## 9. Technical Requirements

### 9.1 Arsitektur
```
Browser (React + Inertia) ──HTTPS──▶ Laravel (Render)
                                       │
              ┌────────────────────────┼─────────────────────────┐
              ▼                        ▼                         ▼
     PostgreSQL (Supabase)     Supabase Storage (poster)     Email provider (SMTP/API)
```
- **Monolit modular**: Controller → (FormRequest, Policy) → Service/Action → Eloquent Model.
- Logika bisnis kritis (registrasi, check-in, pembatalan event) berada di **Service/Action class** agar mudah diuji. CRUD sederhana cukup di controller.
- Komunikasi frontend–backend lewat Inertia (props server-side). Hanya **satu endpoint JSON** yang dibutuhkan: `POST /checkin/scan` (agar scanner tidak me-reload halaman).

### 9.2 Teknologi
| Lapisan | Pilihan | Alasan |
|---|---|---|
| Backend | Laravel (versi stabil terbaru), PHP 8.3+ | Sesuai draft; auth, policy, queue, mail, validation bawaan |
| Frontend | React + Inertia.js + Tailwind CSS | Tanpa API terpisah; Tailwind mempercepat UI |
| Starter kit | **Laravel Breeze (React/Inertia)** | Auth, reset password, verifikasi email sudah jadi; hemat berhari-hari |
| Database | PostgreSQL (Supabase) | Sesuai draft; mendukung partial unique index dan row locking |
| Storage | Supabase Storage (S3-compatible via Flysystem) | Disk Render tidak persisten (C10) |
| QR generate | `simplesoftwareio/simple-qrcode` (SVG) | Matang dan mudah |
| QR scan | `html5-qrcode` di browser | Tanpa hardware khusus; butuh HTTPS (sudah dipenuhi hosting) |
| Chart | `chart.js` + `react-chartjs-2` | Sesuai draft |
| CSV | `League\Csv` atau `response()->streamDownload` | Stream agar hemat memori |
| Email | Resend / Brevo / SMTP lain (free tier) | Tentukan di tahap setup (lihat Open Items) |
| Testing | **Pest** | Ringkas, mendukung Laravel penuh |
| CI | GitHub Actions (jalankan Pest + lint) | Menunjukkan praktik kualitas pada portfolio |

### 9.3 Hosting & deployment
- **Render** (Web Service, Docker) untuk Laravel + hasil build Vite (satu service). Vercel **tidak diperlukan** pada arsitektur monolit **[Perubahan dari draft]**.
- **Supabase**: PostgreSQL + Storage.
- **Catatan koneksi database:** gunakan connection string **session mode / direct**, bukan transaction pooler, karena prepared statements Laravel/PDO bisa bermasalah dengan pooler mode transaksi.
- **Catatan free tier:** web service gratis Render akan *sleep* saat idle (cold start ±30–60 detik). Wajar untuk demo; siapkan catatan di README dan buka aplikasi sebelum demo.
- **Queue:** driver `database`. Karena worker terpisah berbayar di Render, gunakan salah satu: (a) jalankan worker sebagai proses kedua dalam container yang sama (supervisor), atau (b) fallback `dispatch(...)->afterResponse()`. Pilih (a) dahulu; (b) sebagai rencana cadangan agar email tetap tidak memblokir request.
- Konfigurasi lewat environment variables; `.env.example` lengkap di repo.

### 9.4 Keamanan
- Password di-hash (bcrypt/argon2 bawaan Laravel).
- CSRF (otomatis lewat Inertia/Laravel), validasi via FormRequest, escaping default React.
- Rate limit: login, reset password, registrasi event, scan check-in (mis. 60/menit/user).
- Token QR 32+ karakter acak, di-index unik. Respons scan tidak membocorkan data jika token tidak ditemukan.
- Upload: validasi MIME, ukuran, nama file di-generate ulang.
- Semua query data peserta di-scope per event/organizer; policy diuji secara otomatis (lihat 13).
- Aksi destruktif (cancel event, hapus staff) memerlukan konfirmasi.

### 9.5 Performa (secukupnya untuk skala MVP)
- Index: `events(status, start_at)`, `events(slug)`, `tickets(ticket_code)`, `tickets(event_id, status)`, `registrations(event_id, status)`, `registrations(registration_number)`.
- Paginasi pada daftar event, peserta, dan hasil pencarian.
- Hindari N+1 (eager loading, dicek saat code review dan opsional dengan `Model::preventLazyLoading()` di environment lokal).
- Target kualitatif: dashboard dan halaman publik terasa responsif pada ±ribuan registrasi. Angka target tidak ditetapkan, karena akan ditentukan saat testing (sesuai draft).

### 9.6 Konvensi kode
- Mengikuti konvensi Laravel; `Laravel Pint` untuk format.
- Enum PHP untuk status (`EventStatus`, `TicketStatus`, `RegistrationStatus`) agar transisi tidak tersebar sebagai string.
- Migrasi + Seeder + Factory lengkap, sehingga data demo bisa dibuat dengan satu perintah (`php artisan migrate:fresh --seed`).

---

## 10. Model Data

### 10.1 Tabel utama
| Tabel | Kolom penting | Catatan |
|---|---|---|
| `users` | id, name, email (unique), password, role, is_active, email_verified_at, org_name?, org_description? | Profil organisasi cukup kolom opsional (tanpa tabel organizations pada MVP) |
| `categories` | id, name, slug | Dikelola admin |
| `events` | id, organizer_id → users, category_id, title, slug (unique), description, poster_path, format, location, online_url, start_at, end_at, registration_start_at, registration_end_at, status, is_disabled, published_at, cancelled_at | Status disimpan: draft/published/cancelled. "Completed" dihitung |
| `ticket_types` | id, event_id, name, description, quota, available_from, available_until, is_active | Kapasitas event = SUM(quota) |
| `registrations` | id, event_id, user_id, ticket_type_id, registration_number (unique), attendee_name, attendee_email, attendee_phone?, status, cancelled_at | Partial unique `(event_id, user_id) WHERE status='registered'` |
| `tickets` | id, registration_id (unique), event_id, ticket_type_id, ticket_code (unique), status, checked_in_at?, checked_in_by? → users | 1 registrasi = 1 tiket. `event_id` didenormalisasi agar validasi scan cepat |
| `event_staff` | id, event_id, user_id, assigned_by | Unique `(event_id, user_id)` |
| `jobs`, `failed_jobs`, `password_reset_tokens`, `sessions` | bawaan Laravel | Queue dan session database |

### 10.2 Relasi singkat
- User (organizer) 1—N Event; Event 1—N TicketType; Event 1—N Registration; Registration 1—1 Ticket; Event N—N User (staff) lewat `event_staff`; Category 1—N Event.

### 10.3 Penjelasan keputusan
- **Tidak ada tabel `checkins` terpisah** **[Keputusan]**: karena 1 tiket hanya bisa check-in 1 kali, cukup `checked_in_at` dan `checked_in_by` pada `tickets`. Ini juga menghilangkan risiko dua sumber data. Jika kelak ada kebutuhan log percobaan scan gagal, tabel `scan_logs` dapat ditambahkan (Should/Nice).
- **Kuota dihitung, bukan disimpan sebagai counter** **[Keputusan]**: sisa kuota = `quota − COUNT(tickets Active/Checked-in)` di dalam transaksi ber-lock. Tidak ada kolom `remaining` yang bisa tidak sinkron.

---

## 11. Rute & Endpoint Utama

Karena memakai Inertia, sebagian besar rute adalah halaman web. Hanya scan yang berupa JSON.

| Area | Rute (ringkas) | Akses |
|---|---|---|
| Publik | `GET /`, `GET /events`, `GET /events/{slug}` | Semua |
| Auth | `/register`, `/login`, `/logout`, `/forgot-password`, `/reset-password` | Guest/Auth |
| Attendee | `POST /events/{slug}/register`, `GET /my-tickets`, `GET /my-tickets/{ticket}`, *(Should)* `POST /my-registrations/{id}/cancel` | Auth |
| Organizer | `GET /organizer/dashboard`, resource `/organizer/events`, `POST /organizer/events/{e}/publish`, `POST /organizer/events/{e}/cancel` | Organizer |
| | resource `/organizer/events/{e}/ticket-types` | Organizer pemilik |
| | `GET /organizer/events/{e}/attendees`, `GET /organizer/events/{e}/attendees/export` | Organizer pemilik |
| | `GET/POST/DELETE /organizer/events/{e}/staff` | Organizer pemilik |
| Check-in | `GET /checkin`, `GET /checkin/{event}`, `POST /checkin/{event}/scan` (JSON), `GET /checkin/{event}/search`, `POST /checkin/{event}/tickets/{ticket}` (manual) | Staff ditugaskan / organizer pemilik |
| Admin | `/admin/users`, `/admin/organizers`, `/admin/events`, resource `/admin/categories`, *(Should)* `/admin/dashboard` | Admin |

---

## 12. Non-Functional Requirements

| Kategori | Persyaratan |
|---|---|
| Keamanan | Lihat 9.4 |
| Integritas data | Registrasi+tiket atomik; kuota tidak pernah terlampaui; check-in tidak pernah ganda; email gagal tidak membatalkan registrasi |
| Usability | Responsive desktop/tablet/mobile (**desain mobile-first untuk halaman check-in**, karena staff memakai ponsel); validasi form jelas; status berwarna konsisten; halaman scan minim elemen |
| Keandalan | Error handling jelas (halaman 403/404/500 kustom, pesan validasi spesifik pada scan); logging error; email *retry* (3 kali) lewat queue |
| Maintainability | Business logic di Service/Action; test untuk alur kritis; `.env.example`; README setup & deploy |
| Aksesibilitas dasar | Kontras warna cukup, label form, status tidak hanya dibedakan lewat warna (ikon + teks) |

---

## 13. Strategi Testing

Fokus pada **alur kritis**, bukan mengejar persentase coverage.

| Area | Test wajib (Pest) |
|---|---|
| Registrasi | Berhasil → registrasi + tiket + token terbuat · event Draft/Cancelled ditolak · di luar periode ditolak · kuota habis ditolak · duplikat ditolak · ticket type event lain ditolak |
| Kuota (konkurensi) | Kuota 1, dua permintaan hampir bersamaan → hanya satu sukses. (Gunakan test transaksi ber-lock; verifikasi tambahan manual dengan dua proses paralel) |
| Check-in | Tiket valid sukses · tiket event lain ditolak · tiket terpakai ditolak (dengan info waktu/petugas) · tiket batal ditolak · di luar jendela waktu ditolak · manual check-in aturan sama · pemanggilan ganda hanya menghasilkan satu check-in |
| Otorisasi | Organizer tidak bisa melihat/mengubah event milik lain · staff hanya event ditugaskan · attendee tidak bisa membuka tiket orang lain · attendee tidak bisa akses halaman organizer/admin · staff tidak bisa akses analytics |
| Event | Publish gagal jika data tak lengkap · cancel event membatalkan semua tiket dan menutup registrasi · kuota tidak bisa diturunkan di bawah tiket terbit |
| Dashboard | Angka total/attendance rate sesuai data seeded |
| Email | Mail ter-*queue* setelah registrasi (`Mail::fake`/`Queue::fake`) dan kegagalan email tidak membatalkan registrasi |
| Ekspor | CSV berisi kolom benar dan hanya data event milik organizer |

CI GitHub Actions menjalankan test setiap push/PR.

---

## 14. Roadmap Pengembangan

> **Asumsi waktu:** ±15–20 jam/minggu → **±10 minggu**. Jika waktu Anda berbeda, skalakan durasi; **urutan dan kriteria selesai tidak berubah**. Tiap fase menghasilkan sesuatu yang bisa didemokan.
> Urutan disusun dari **alur inti dahulu**, **Admin di akhir** (D3), dan Should Have hanya setelah Must selesai.

| Fase | Minggu | Fokus | Output / Kriteria selesai |
|---|---|---|---|
| **0. Setup** | 1 | Repo GitHub, Laravel + Breeze (Inertia React), Tailwind, koneksi Supabase, Pest + Pint, GitHub Actions, `.env.example`, deploy "hello world" ke Render | Aplikasi kosong ter-deploy, CI hijau, migrasi berjalan di Supabase |
| **1. Auth & Role** | 2 | Role (`admin/organizer/attendee`), registrasi dengan opsi organizer, policy dasar, layout per role, seeder admin | Tiga jenis user dapat login dan melihat dashboard kosong yang sesuai; test otorisasi dasar lulus |
| **2. Event & Ticket Type** | 3–4 | Kategori (seeder dulu), CRUD event, draft/publish/cancel, upload poster (Supabase Storage), CRUD ticket type + validasi publish, enum status | Organizer membuat, mempublikasikan, dan membatalkan event; test aturan event lulus |
| **3. Public & Registrasi** | 5–6 | Halaman daftar & detail event, alur login-lalu-kembali, `RegistrationService` (transaksi + lock), pembuatan tiket + token, My Tickets, halaman tiket + QR, email via queue | Peserta daftar → tiket muncul → email terkirim; **test kuota & duplikat lulus** |
| **4. Staff & Check-in** | 7 | Penugasan staff via email, halaman check-in mobile-first, scanner kamera, `CheckInService`, pencarian & manual check-in | Alur check-in end-to-end di ponsel; **test check-in ganda & otorisasi lulus** |
| **5. Dashboard & Ekspor** | 8 | Dashboard organizer & per event (angka), daftar peserta berpaginasi, ekspor CSV | Angka sesuai data seeded; CSV valid |
| **6. Admin** | 9 | Daftar user/organizer/event, CRUD kategori; lalu (Should) dashboard platform & nonaktifkan | Poin acceptance admin terpenuhi |
| **7. Hardening & Rilis** | 10 | Should Have terpilih (urut: filter/pencarian event, pembatalan registrasi, chart, email verification), perapihan UI/UX & error page, seeder data demo realistis, README (setup, arsitektur, keputusan), screenshot/GIF demo, uji manual end-to-end, deploy final | Seluruh acceptance criteria terpenuhi, demo publik berjalan |

### 14.1 Aturan cut-line (menjaga scope)
Jika jadwal tertinggal, **potong dari bawah** dengan urutan berikut (jangan pernah memotong Must di fase 1–5):
1. Chart tren/distribusi (tetap tampilkan angka)
2. Aktivitas platform & dashboard admin
3. Nonaktifkan user/event
4. Pembatalan registrasi oleh peserta
5. Profil organisasi

### 14.2 Definition of Done per fitur
Requirement terimplementasi · validasi backend · otorisasi (policy) · error handling · UI sesuai flow (termasuk mobile) · test perilaku kritis lulus · tidak ada bug blocker · dokumentasi diperbarui jika relevan.

---

## 15. Acceptance Criteria MVP

MVP siap didemokan bila:

1. Organizer dapat mendaftar dan login.
2. Organizer dapat membuat event dan menyimpannya sebagai draft.
3. Event valid dapat dipublikasikan; event tidak lengkap ditolak dengan pesan jelas.
4. Event published tampil di halaman publik; draft/cancelled tidak.
5. Peserta (berakun) dapat melihat detail dan mendaftar; belum login diarahkan lalu kembali ke event.
6. Sistem menolak registrasi di luar periode, kuota habis, event tidak aktif, dan duplikat.
7. Registrasi sukses menghasilkan **satu tiket unik ber-QR** dan nomor registrasi.
8. Peserta melihat tiketnya di My Tickets dan tidak dapat melihat tiket orang lain.
9. Email tiket terkirim lewat antrean; kegagalan email tidak membatalkan registrasi.
10. Organizer dapat menambahkan staff (via email akun terdaftar) dan menghapusnya.
11. Staff hanya melihat event yang ditugaskan.
12. Staff dapat check-in lewat scan QR di ponsel dan melalui pencarian manual.
13. Sistem menolak tiket invalid, tiket event lain, tiket batal, dan **check-in ganda** (dengan alasan jelas).
14. Organizer melihat statistik kehadiran dan daftar peserta, serta mengekspor CSV.
15. Pembatalan event membatalkan seluruh tiket dan memberi notifikasi.
16. Admin dapat melihat user, organizer, event, dan mengelola kategori.
17. Seluruh test alur kritis (bagian 13) lulus di CI.
18. README memuat setup lokal, deployment, dan akun demo; aplikasi dapat diakses lewat URL demo.

---

## 16. Risiko & Mitigasi

| ID | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R1 | Admin penuh seperti draft menambah waktu | Jadwal mundur | Dikerjakan terakhir; cut-line 14.1 |
| R2 | Oversell kuota karena race condition | Integritas rusak | Row lock dalam transaksi + test konkurensi |
| R3 | Check-in ganda | Data kehadiran salah | `UPDATE ... WHERE status='active'` + cek affected rows |
| R4 | Kamera browser tidak bisa scan (izin/HTTPS/perangkat) | Check-in terhambat | Hosting HTTPS; **pencarian manual sebagai cadangan P0**; uji di ≥ 2 ponsel sejak fase 4 |
| R5 | Queue tidak berjalan di Render gratis | Email tidak terkirim | Worker dalam container yang sama; fallback `afterResponse` |
| R6 | Cold start Render mengganggu demo | Kesan buruk | Catatan di README; *wake-up* sebelum demo |
| R7 | Poster hilang karena disk ephemeral | Gambar rusak | Supabase Storage |
| R8 | Masalah pooler Supabase dengan PDO | Error query aneh | Gunakan koneksi session/direct |
| R9 | Perubahan event setelah tiket terbit | Peserta bingung | Batasan edit (7.2) + email pembaruan (Should) |
| R10 | Scope creep | Proyek tak selesai | Out of scope terkunci; fitur baru hanya masuk lewat Nice to Have |
| R11 | Akses data lintas pengguna | Kebocoran data | Policy + query ter-scope + test otorisasi |
| R12 | Penyalahgunaan akun (email palsu) | Data kotor | Email verification (Should), rate limiting |

---

## 17. Open Items

Hal-hal ini tidak menghalangi mulai mengerjakan, tetapi perlu diputuskan pada waktunya:

| # | Pertanyaan | Kapan perlu diputuskan | Default yang saya sarankan |
|---|---|---|---|
| O1 | Provider email mana yang dipakai (Resend, Brevo, Mailtrap untuk dev)? | Fase 0 | Mailtrap untuk dev, Resend/Brevo untuk demo |
| O2 | Berapa jam per minggu yang tersedia? Ini menentukan skala jadwal roadmap | Sekarang | Asumsi 15–20 jam/minggu |
| O3 | Jendela check-in (2 jam sebelum acara sampai selesai) sudah sesuai? | Fase 4 | Konstanta yang dapat diubah |
| O4 | Apakah organizer baru perlu persetujuan admin? | Fase 6 | Tidak perlu pada MVP |
| O5 | Bahasa antarmuka aplikasi: Indonesia, Inggris, atau keduanya? | Fase 2 | Satu bahasa saja (tanpa i18n) agar sederhana |
| O6 | Nama dan identitas visual (logo, warna)? | Fase 7 | Palet sederhana via Tailwind |

---

**End of PRD — Gatherly v1.1**
