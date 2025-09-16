<!-- Topologi -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingTopologi">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTopologi" aria-expanded="false" aria-controls="collapseTopologi">
            <b>Topologi</b>
        </button>
    </h2>
    <div id="collapseTopologi" class="accordion-collapse collapse" aria-labelledby="headingTopologi" data-bs-parent="#accordionDocs">
        <div class="accordion-body">
            <div class="row mb-2 mt-2">
                <div class="col-12 text-center">
                    <img src="assets/image/topologi.png" alt="Topologi Sistem" class="img-fluid rounded border-1" width="100%">
                    <small>Gambar 1. Tologoi Sistem</small>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    Penjelasan alur sistem:
                    <ul>
                        <li><small>Developer coding & testing aplikasi di http://localhost.</small></li>
                        <li><small>Saat butuh Snap Token, aplikasi tidak bisa langsung request ke Midtrans (karena Midtrans hanya izinkan HTTPS).</small></li>
                        <li><small>Maka aplikasi local mengirim request ke Proxy (SnapBridge) yang sudah menggunakan HTTPS.</small></li>
                        <li><small>SnapBridge akan meneruskan request ke Midtrans API dengan API Key yang aman tersimpan di server.</small></li>
                        <li><small>Midtrans mengembalikan Snap Token ke SnapBridge.</small></li>
                        <li><small>SnapBridge meneruskan token itu kembali ke aplikasi local.</small></li>
                    </ul>
                </div>
            </div>
           
            
        </div>
    </div>
</div>