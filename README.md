# Product Manager - Web Application

Aplikasi web sederhana untuk manajemen produk (CRUD) yang dibangun menggunakan PHP murni, MySQL/PDO, dan Tailwind CSS.

## 🛠️ Fitur Utama
- CRUD Produk (Tambah, Edit, Hapus, Tampil dalam bentuk Card responsif)
- Validasi Input Server-Side (Nama min 3 karakter, harga > 0, stok >= 0, nama unik)
- Proteksi Keamanan (XSS Protection via htmlspecialchars, CSRF Token, Prepared Statements PDO)
- PRG Pattern (Mencegah duplikasi data saat refresh)

## 📂 Struktur Berkas
- `db.php`: Koneksi database PDO
- `csrf.php`: Pengelolaan token CSRF
- `index.php`: Logika CRUD dan antarmuka UI
- `README.md`: Dokumentasi proyek