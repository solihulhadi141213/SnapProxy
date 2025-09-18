<?php
    header('Content-Type: application/json');

    // Include file koneksi
    include "../_Config/Connection.php";

    // Cek metode request harus GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([
            "status" => "Invalid request method",
            "code" => 405,
            "metadata" => null
        ]);
        exit;
    }

    // Ambil x-token dari header
    $headers = getallheaders();
    if (!isset($headers['x-token'])) {
        echo json_encode([
            "status" => "x-token required",
            "code" => 401,
            "metadata" => null
        ]);
        exit;
    }

    $x_token = trim($headers['x-token']);

    // Validasi token di tabel api_token
    $sql = "SELECT id_account, datetime_expired FROM api_token WHERE api_token = ?";
    $stmt = $Conn->prepare($sql);
    $stmt->bind_param("s", $x_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            "status" => "Invalid token",
            "code" => 401,
            "metadata" => null
        ]);
        exit;
    }

    $row = $result->fetch_assoc();
    $id_account = $row['id_account'];
    $datetime_expired = $row['datetime_expired'];

    // Cek apakah token sudah expired
    if (strtotime($datetime_expired) < time()) {
        echo json_encode([
            "status" => "Token expired",
            "code" => 401,
            "metadata" => null
        ]);
        exit;
    }

    // Ambil data setting berdasarkan id_account
    $sql_setting = "SELECT * FROM setting_payment WHERE id_account = ?";
    $stmt_setting = $Conn->prepare($sql_setting);
    $stmt_setting->bind_param("s", $id_account);
    $stmt_setting->execute();
    $res_setting = $stmt_setting->get_result();

    $settings = [];
    while ($row_setting = $res_setting->fetch_assoc()) {
        $settings[] = $row_setting;
    }
    
    //Header
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (10 * 60)));
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header('Content-Type: application/json');
    header('Pragma: no-cache');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, x-token, token"); 
    
    // Response sukses
    echo json_encode([
        "status" => "success",
        "code" => 200,
        "metadata" => $settings
    ]);
?>
