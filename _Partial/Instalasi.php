<!-- Instalasi -->
<div class="accordion-item">
    <h2 class="accordion-header" id="headingInstalasi">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInstalasi" aria-expanded="false" aria-controls="collapseInstalasi">
            <b>Cara Instal</b>
        </button>
    </h2>
    <div id="collapseInstalasi" class="accordion-collapse collapse" aria-labelledby="headingInstalasi" data-bs-parent="#accordionDocs">
        <div class="accordion-body">
            <div class="alert alert-info">
                <small>
                    <b>Penting!</b><br>
                    Aplikasi ini hanya akan bisa berjalan dengan baik pada webserver dengan koneksi aman (dilengkapi dengan SSL) menggunakan <i>https</i>. <br>
                    Disarankan untuk menggunakan web hosting atau VPS yang memiliki sertifikat SSL dengan koneksi aman, kemudian anda bisa 
                    menggunakan <i>endpoint</i> aplikasi ini (sesuai dokumentasi) dari aplikasi lokal yang sedang anda kembangkan.
                </small>
            </div>
            <small>
                <ol>
                    <li>Clone atau Download repository dari GitHub : <code class="text text-secondary">https://github.com/solihulhadi141213/SnapProxy</code></li>
                    <li>Import database dari file SQL pada directory di aplikasi ini : <code class="text text-secondary">DB/SNAP_PROXY.sql</code></li></li>
                    <li>Sesuaikan konfigurasi pada <code class="text text-secondary">_Config/Connection.php</code>.</li>
                    <li>
                        Buka file <code class="text text-secondary">_Config/Connection.php</code> tersebut dan lakukan penyesuaian sebagai berikut.
                        <ul>
                            <li>
                                <b>$servername </b> diisi dengan nama server (secara default diisi dengan  <code class="text text-secondary">localhost</code>)
                            </li>
                            <li>
                                <b>$username </b> diisi dengan username database (secara default diisi dengan  <code class="text text-secondary">root</code>)
                            </li>
                            <li>
                                <b>$password </b> diisi dengan password database (secara default pada webservel local dapat dikosongkan)
                            </li>
                            <li>
                                <b>$db </b> Sesuaikan dengan nama database yang digunakan (misalnya SNAP_PROXY)
                            </li>
                        </ul>
                    </li>
                    <li>
Berikut ini adalah contoh pengisian script Connection.php :
<pre><code class="language-php">&lt;?php
    $servername = "localhost";
    $username   = "your_username";
    $password   = "your_password";
    $db         = "SNAP_PROXY";

    $Conn = new mysqli($servername, $username, $password, $db);

    if ($Conn->connect_error) {
        die("Connection failed: " . $Conn->connect_error);
    }
?&gt;</code></pre>
                    </li>
                    <li>Jalankan dengan mengunjungi url pada browser dengan contoh alamat : <code class="text text-secondary">http://alamat_domain.com/SnapProxy/index.php</code></li>
                    <li>Atau anda bisa memasang aplikasi ini pada directory utama subdomain anda dengan contoh alamat : <code class="text text-secondary">http://SnapProxy.alamat_domain.com/index.php</code></li>
                    <li>Jika berhasil anda akan diarahkan ke halaman dokumentasi aplikasi.</li>
                    <li>
                        Akun default :
                        <ul>
                            <li>
                                USER_KEY : <code class="text text-secondary">3mQKUd4ikicxxG3EQHVy6LcjSiHV8IlRXYgP</code>
                            </li>
                            <li>
                                SECRET_KEY : <code class="text text-secondary">1VttZESUj7m2l1cLOq2nYUl6wpZddWw4tEOq</code>
                            </li>
                        </ul>
                    </li>
                </ol>
            </small>
        </div>
    </div>
</div>