<?php
    // Mulai session dan set timezone
    session_start();
    date_default_timezone_set('Asia/Jakarta');

    // Inisialisasi variabel
    $id_account_session = "";
    $session_token = "";

    // Validasi session
    if (!empty($_SESSION["id_account_session"]) && !empty($_SESSION["session_token"])) {
        $id_account_session = $_SESSION["id_account_session"];
        $session_token      = $_SESSION["session_token"];

        // Validasi Token Akses menggunakan prepared statement
        $QryAksesLogin = $Conn->prepare("SELECT * FROM account_session WHERE id_account_session = ? AND session_token = ?");
        $SessionModeAkses = "default"; // Pastikan variabel ini ada dan sesuai dengan kebutuhan
        $QryAksesLogin->bind_param("ss", $id_account_session, $session_token);
        $QryAksesLogin->execute();
        $ResultAksesLogin = $QryAksesLogin->get_result();
        $DataAksesLogin = $ResultAksesLogin->fetch_assoc();
        $QryAksesLogin->close();

        // Jika Data Ditemukan
        if ($DataAksesLogin) {
            $SessionDateExpired = $DataAksesLogin['session_expired'];
            $DateSekarang = date('Y-m-d H:i:s');

            // Periksa apakah token masih berlaku
            if ($SessionDateExpired >= $DateSekarang) {
                $expired_milliseconds = 1000 * 60 * 60; // 1 jam dalam milidetik
                $date_expired_new = date('Y-m-d H:i:s', strtotime("+1 hour")); // Hitung waktu expired baru

                // Update token dengan prepared statement
                $UpdateToken = $Conn->prepare("UPDATE account_session SET session_expired = ? WHERE id_account_session = ?");
                $UpdateToken->bind_param("ss", $date_expired_new, $id_account_session);
                if ($UpdateToken->execute()) {
                    $id_account_session     = $DataAksesLogin['id_account_session'];
                    $session_token          = $DataAksesLogin['session_token'];
                } else {
                    // Jika update gagal, reset session
                    $id_account_session = "";
                    $session_token      = "";
                }
                $UpdateToken->close();
            } else {
                // Token expired
                $id_account_session     = "";
                $session_token          = "";
            }
        } else {
            // Jika data tidak ditemukan
            $id_account_session = "";
            $session_token = "";
        }
    }
?>
