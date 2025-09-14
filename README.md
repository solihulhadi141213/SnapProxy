# SnapProxy
SnapBridge adalah aplikasi pelantara (proxy gateway) yang berfungsi sebagai jembatan antara lingkungan pengembangan lokal dengan layanan Midtrans Payment Gateway. Midtrans mewajibkan koneksi HTTPS untuk melakukan generate Snap Token, sedangkan banyak developer masih melakukan pengembangan di http://localhost. Dengan adanya SnapBridge, developer dapat:
- Menghubungkan aplikasi lokal ke Midtrans melalui server proxy ber-HTTPS.
- Menghasilkan Snap Token secara aman tanpa harus deploy ke server publik.
- Mempercepat proses testing dan integrasi pembayaran di fase development.
- Menyediakan endpoint sederhana yang mudah dipanggil oleh aplikasi lokal.