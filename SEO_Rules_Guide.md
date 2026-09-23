# Panduan Sistem SEO Rules (Dynamic Checker)

Sistem SEO Rules pada aplikasi ini didesain secara **dinamis**. Artinya, Anda tidak perlu membongkar kode PHP setiap kali ingin menambahkan kriteria pengecekan baru. Semuanya dikendalikan melalui panel **SEO Rules** menggunakan kombinasi **HTML Selectors** dan **Tipe Rule**.

Berikut adalah penjelasan cara kerjanya dan panduan membuat rule baru secara manual.

---

## ⚙️ Konsep Dasar Cara Kerja

Saat Anda mengeklik "Run Checker", sistem melakukan langkah-langkah berikut:
1. **Download HTML:** Sistem mengunduh *source code* HTML dari URL yang dimasukkan.
2. **Ambil Rule Aktif:** Sistem mengambil semua rules yang berstatus "Active" di Database.
3. **Pencarian Elemen (Selector):** Untuk setiap rule, sistem mencari elemen di dalam HTML menggunakan **CSS Selector** (seperti yang biasa dipakai di jQuery atau CSS, misal: `h1`, `meta[name="description"]`, `link[rel="canonical"]`).
4. **Evaluasi (Rule Type):** Setelah elemen ditemukan, sistem menjalankan evaluasi berdasarkan **Type** (misalnya mengukur panjang teksnya, menghitung jumlahnya, atau mengecek ketiadaannya).
5. **Keputusan (Severity):** Jika gagal, sistem akan mengeluarkan *Error* (Merah) atau sekadar *Warning* (Kuning) sesuai tingkat keparahan yang Anda tentukan.

---

## 🛠️ Cara Menambahkan Rule Manual (Penjelasan per Tipe)

Ketika Anda mengeklik tombol biru **+ Add New Rule**, Anda akan diminta mengisi beberapa kolom. Bagian yang paling penting adalah **Type** (Tipe Pengecekan) dan **Configuration** (Syaratnya). 

Berikut adalah 6 tipe rule yang tersedia beserta contoh penggunaannya:

### 1. Type: `exist` (Cek Keberadaan Elemen)
Digunakan ketika Anda hanya ingin memastikan sebuah elemen **ada** di dalam HTML, tanpa peduli isinya apa.
- **Kasus Penggunaan:** Mengecek apakah halaman memiliki tag `<link rel="canonical">`.
- **Selector:** `link[rel="canonical"]`
- **Configuration (JSON):** *(Kosongkan saja, atau `{}`)*

### 2. Type: `count` (Cek Jumlah Elemen)
Digunakan ketika sebuah elemen harus muncul dalam jumlah yang spesifik (tidak boleh kurang, tidak boleh lebih).
- **Kasus Penggunaan:** Memastikan hanya ada persis **satu** tag `<h1>` di dalam halaman.
- **Selector:** `h1`
- **Configuration (JSON):** 
  ```json
  {
    "expected_count": 1
  }
  ```

### 3. Type: `length` (Cek Panjang Karakter)
Digunakan untuk mengecek panjang teks di dalam elemen atau atribut.
- **Kasus Penggunaan:** Meta description SEO yang ideal panjangnya antara 120 sampai 160 karakter.
- **Selector:** `meta[name="description"]`
- **Attribute:** `content` *(Karena kita ingin menghitung panjang teks di dalam atribut content="..." bukan di dalam tag)*
- **Configuration (JSON):** 
  ```json
  {
    "min": 120,
    "max": 160
  }
  ```
  *(Catatan: Anda bisa menggunakan "min" saja, "max" saja, atau keduanya).*

### 4. Type: `text_match` (Pencocokan Teks Persis)
Digunakan jika Anda ingin isi teks atau atribut harus **sama persis** dengan yang Anda inginkan.
- **Kasus Penggunaan:** Ingin memastikan bahasa dokumen diset ke bahasa Indonesia (`<html lang="id">`).
- **Selector:** `html`
- **Attribute:** `lang`
- **Configuration (JSON):** 
  ```json
  {
    "expected_text": "id",
    "exact_match": true
  }
  ```

### 5. Type: `regex` (Pola Teks / Regular Expression)
Digunakan untuk pengecekan pola tingkat lanjut.
- **Kasus Penggunaan:** Memastikan struktur URL pada *canonical* tidak mengandung awalan `http://` (harus `https://`).
- **Selector:** `link[rel="canonical"]`
- **Attribute:** `href`
- **Configuration (JSON):** 
  ```json
  {
    "pattern": "/^https:\\/\\//i"
  }
  ```

### 6. Type: `compare_amp` (Komparasi Landing Page vs AMP)
Digunakan secara khusus untuk memastikan bahwa isi tag di halaman utama (Landing Page) sama persis dengan halaman AMP-nya. Jika rule ini aktif, maka form pengisian **AMP URL** di halaman depan wajib diisi!
- **Kasus Penggunaan:** Memastikan teks di tag `<title>` versi biasa tidak berbeda dengan `<title>` versi AMP.
- **Selector:** `title`
- **Configuration (JSON):** *(Kosongkan saja, atau `{}`)*

---

## 💡 Tips Penting
1. **Atribut (Attribute Field):** Jika kosong, sistem akan mengambil *inner text* (teks yang diapit oleh tag, contoh `<h1>Teks Ini</h1>`). Jika Anda mengisi field attribute (misal: `content`), sistem akan mengekstrak nilai atribut tersebut (contoh: `<meta content="Teks Ini">`).
2. **Rule Mati/Hidup:** Anda tidak perlu menghapus rule jika sedang tidak dipakai, cukup ubah statusnya menjadi **Inactive**.
3. **Severity:** Pilih **Error** jika kegagalan rule ini berakibat fatal secara SEO. Pilih **Warning** jika itu sekadar anjuran praktik terbaik (best practice).
