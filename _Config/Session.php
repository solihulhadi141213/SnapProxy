<?php
    // Mulai session dan set timezone
    session_start();
    date_default_timezone_set('Asia/Jakarta');

    // Inisialisasi variabel
    $SessionIdAkses = "";
    $SessionLoginToken = "";

    // Validasi session
    if (!empty($_SESSION["id_akses"]) && !empty($_SESSION["login_token"])) {
        $SessionIdAkses = $_SESSION["id_akses"];
        $SessionLoginToken = $_SESSION["login_token"];

        // Validasi Token Akses menggunakan prepared statement
        $QryAksesLogin = $Conn->prepare("
            SELECT id_akses, date_creat, date_expired, token 
            FROM akses_login 
            WHERE id_akses = ? AND token = ?
        ");
        $SessionModeAkses = "default"; // Pastikan variabel ini ada dan sesuai dengan kebutuhan
        $QryAksesLogin->bind_param("is", $SessionIdAkses, $SessionLoginToken);
        $QryAksesLogin->execute();
        $ResultAksesLogin = $QryAksesLogin->get_result();
        $DataAksesLogin = $ResultAksesLogin->fetch_assoc();
        $QryAksesLogin->close();

        // Validasi hasil query
        if ($DataAksesLogin) {
            $SessionDateExpired = $DataAksesLogin['date_expired'];
            $DateSekarang = date('Y-m-d H:i:s');

            // Periksa apakah token masih berlaku
            if ($SessionDateExpired >= $DateSekarang) {
                $expired_milliseconds = 1000 * 60 * 60; // 1 jam dalam milidetik
                $date_expired_new = date('Y-m-d H:i:s', strtotime("+1 hour")); // Hitung waktu expired baru

                // Update token dengan prepared statement
                $UpdateToken = $Conn->prepare("
                    UPDATE akses_login 
                    SET date_expired = ? 
                    WHERE id_akses = ?
                ");
                $UpdateToken->bind_param("ss", $date_expired_new, $SessionIdAkses);
                if ($UpdateToken->execute()) {
                    $SessionLoginToken = $DataAksesLogin['token'];
                } else {
                    // Jika update gagal, reset session
                    $SessionIdAkses = "";
                    $SessionLoginToken = "";
                }
                $UpdateToken->close();
            } else {
                // Token expired
                $SessionIdAkses = "";
                $SessionLoginToken = "";
            }
        } else {
            // Jika data tidak ditemukan
            $SessionIdAkses = "";
            $SessionLoginToken = "";
        }
    }
?>
