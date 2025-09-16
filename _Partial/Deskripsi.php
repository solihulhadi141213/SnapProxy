<!-- Deskripsi -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingDeskripsi">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDeskripsi" aria-expanded="true" aria-controls="collapseDeskripsi">
            <b>Deskripsi</b>
        </button>
    </h2>
    <div id="collapseDeskripsi" class="accordion-collapse collapse show" aria-labelledby="headingDeskripsi" data-bs-parent="#accordionDocs">
        <div class="accordion-body">
            <small>
                <p>
                    <b>SnapProxy</b> adalah sebuah aplikasi proxy gateway yang dirancang untuk mempermudah integrasi layanan pembayaran menggunakan <b>Midtrans Payment Gateway</b> di lingkungan pengembangan lokal.
                </p>
                <p>
                    Midtrans secara default mewajibkan penggunaan protokol HTTPS untuk proses otentikasi dan pembuatan Snap Token. 
                    Hal ini seringkali menyulitkan developer yang melakukan uji coba di lingkungan <code class="text text-secondary">http://localhost</code>
                </p>
                <p>
                    Dengan <b>SnapProxy</b>, permasalahan tersebut dapat diatasi melalui mekanisme perantara (reverse proxy) yang aman dan transparan. 
                    Aplikasi ini bertindak sebagai jembatan antara aplikasi lokal developer (yang berjalan di HTTP) dengan server Midtrans (yang mewajibkan HTTPS).
                </p>
                <p>
                    <span class="text text-decoration-underline">Fitur Utama :</span>
                    <ol>
                        <li>Mengalihkan request dari localhost tanpa harus memasang SSL sendiri.</li>
                        <li>Developer cukup mengarahkan request API ke <b>SnapProxy</b>, kemudian sistem meneruskan ke Midtrans.</li>
                        <li>Memungkinkan pengujian Snap Token, transaksi, dan callback tanpa perlu deploy ke server ber-SSL.</li>
                        <li>Mendukung pencatatan request/response untuk mempermudah proses debugging.</li>
                        <li>Hanya perlu mengatur API Key Midtrans dan endpoint proxy.</li>
                    </ol>
                </p>
                <p>
                    <span class="text text-decoration-underline">Manfaat :</span>
                    <ol>
                        <li>Mempercepat proses development & testing integrasi Midtrans.</li>
                        <li>Menghilangkan kebutuhan server SSL lokal yang rumit.</li>
                        <li>Menjadi solusi ringan dan praktis untuk tim IT dan developer yang ingin menjaga kesesuaian dengan standar keamanan Midtrans tanpa menghambat workflow pengembangan.</li>
                    </ol>
                </p>
            
            </small>
        </div>
    </div>
</div>