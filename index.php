<?php
    //Koneksi dan Session
    include "_Config/Connection.php"; 
    include "_Config/Session.php"; 
    // Tentukan protocol (http/https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
        || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    // Ambil host (domain)
    $domain = $_SERVER['HTTP_HOST'];

    // Base URL
    $base_url = $protocol . $domain;
?>
<!DOCTYPE html>
<html lang="id">
    <?php 
        //Head (Title, Favicon, Metatag)
        include "_Partial/Head.php"; 
    ?>
    <body>

        <div class="container">
            <?php
                //Header
                include "_Partial/Header.php"; 
            ?>

            <div class="accordion" id="accordionDocs">

                <?php
                    //Deskripsi
                    include "_Partial/Deskripsi.php"; 

                    //Topologi
                    include "_Partial/Topologi.php"; 

                    //Spesifikasi
                    include "_Partial/Spesifikasi.php"; 

                    //Instalasi
                    include "_Partial/Instalasi.php";

                    //Periapan
                    include "_Partial/Periapan.php"; 

                    //Dokumentasi API
                    include "_Partial/Dokumentasi.php"; 

                    //Tools
                    include "_Partial/Tools.php"; 

                    //Interactive Aplication
                    include "_Partial/Aplication.php"; 

                    //Footer
                    include "_Partial/Footer.php"; 
                ?>
            </div>
        </div>
        
        <!-- JQuery -->
        <script src="node_modules/jquery/dist/jquery.min.js"></script>

        <!-- Bootstrap JS -->
        <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Custome Script -->
         <script src="_Page/Home/Home.js?v=1"></script>

    </body>
</html>
