<!DOCTYPE html>
<html lang="id">
    <?php include "_Partial/Head.php"; ?>
    <body>
        <div class="container-fluid p-4">
            <header class="mb-4 text-center">
                <h1 class="h3 text-dark">Snap Proxy</h1>
            </header>

            <main>
            <div class="section">
                <h2>1. Deskripsi Aplikasi</h2>
                SnapBridge adalah aplikasi pelantara (proxy gateway) yang berfungsi sebagai jembatan antara lingkungan pengembangan lokal dengan layanan Midtrans Payment Gateway.
                Midtrans mewajibkan koneksi HTTPS untuk melakukan generate Snap Token, sedangkan banyak developer masih melakukan pengembangan di http://localhost.
                Dengan adanya SnapBridge, developer dapat:
                <ul>
                    <li>Menghubungkan aplikasi lokal ke Midtrans melalui server proxy ber-HTTPS.</li>
                    <li>Menghasilkan Snap Token secara aman tanpa harus deploy ke server publik.</li>
                    <li>Mempercepat proses testing dan integrasi pembayaran di fase development.</li>
                    <li>Menyediakan endpoint sederhana yang mudah dipanggil oleh aplikasi lokal.</li>
                </ul>
            </div>

            <div class="section">
                <h2>2. Struktur Directory Aplikasi</h2>
                <pre>
        /root-folder
        │── index.html
        │── /assets
        │   ├── css/
        │   ├── js/
        │   └── img/
        │── /api
        │   ├── service1.php
        │   └── service2.php
        │── /config
        │   └── database.php
                </pre>
            </div>

            <div class="section">
                <h2>3. Spesifikasi Teknologi</h2>
                <ul>
                <li>PHP 7.4 / 8.x</li>
                <li>Database: MySQL / MariaDB</li>
                <li>Frontend: HTML5, CSS3, JavaScript (jQuery)</li>
                <li>Library: Bootstrap, jQuery, Chart.js (opsional)</li>
                <li>Server: Apache / Nginx</li>
                </ul>
            </div>

            <div class="section">
                <h2>4. Topologi Sistem</h2>
                <p>Letakkan gambar topologi di sini:</p>
                <img src="assets/img/topologi.png" alt="Topologi Sistem" class="img-fluid border rounded">
            </div>

            <div class="section">
                <h2>5. Cara Instalasi</h2>
                <ol>
                <li>Clone repository atau ekstrak file aplikasi.</li>
                <li>Letakkan file di folder <code>htdocs</code> atau <code>www</code>.</li>
                <li>Buat database di MySQL sesuai konfigurasi di <code>config/database.php</code>.</li>
                <li>Import file <code>database.sql</code>.</li>
                <li>Akses aplikasi via <code>http://localhost/nama_aplikasi</code>.</li>
                </ol>
            </div>

            <div class="section">
                <h2>6. Dokumentasi API Service</h2>
                <ul>
                <li><strong>GET</strong> <code>/api/service1.php</code> → Mendapatkan data</li>
                <li><strong>POST</strong> <code>/api/service2.php</code> → Menyimpan data</li>
                </ul>
                <p>Contoh response JSON:</p>
                <pre>
        {
        "status": "success",
        "message": "Data berhasil diambil",
        "data": [...]
        }
                </pre>
            </div>

            <div class="section">
                <h2>7. Author</h2>
                <p><strong>Nama:</strong> John Doe</p>
                <p><strong>Email:</strong> johndoe@email.com</p>
                <p><strong>Kontak:</strong> +62 812 3456 7890</p>
            </div>

            <div class="section">
                <h2>8. Informasi Lain</h2>
                <p>Versi Aplikasi: 1.0.0</p>
                <p>Tanggal Rilis: September 2025</p>
                <p>Lisensi: MIT License</p>
            </div>
            </main>

            <footer>
            <p>&copy; 2025 - Template Informasi Aplikasi</p>
            <p>Dokumentasi dibuat untuk mempermudah pengembangan dan pemeliharaan sistem.</p>
            </footer>
        </div>

        <!-- Bootstrap JS Bundle -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
