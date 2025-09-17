<?php
    header('Content-Type: application/json');

    // Include file koneksi
    include "../_Config/Connection.php";
    include "../_Config/Function.php";

    // Cek metode request harus POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            "status" => "Invalid request method",
            "code" => 405,
            "metadata" => null
        ]);
        exit;
    }

    // Ambil input JSON
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // Validasi input
    if (!isset($data['USER_KEY']) || !isset($data['SECRET_KEY'])) {
        echo json_encode([
            "status" => "USER_KEY or SECRET_KEY required",
            "code" => 400,
            "metadata" => null
        ]);
        exit;
    }

    $USER_KEY   = trim($data['USER_KEY']);
    $SECRET_KEY = trim($data['SECRET_KEY']);

    // Query cek USER_KEY di database
    $sql = "SELECT id_account, user_key, secret_key FROM api_account WHERE user_key = ?";
    $stmt = $Conn->prepare($sql);
    $stmt->bind_param("s", $USER_KEY);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            "status" => "USER_KEY invalid",
            "code" => 401,
            "metadata" => null
        ]);
        exit;
    }

    $row = $result->fetch_assoc();

    // Validasi SECRET_KEY dengan bcrypt
    if (!password_verify($SECRET_KEY, $row['secret_key'])) {
        echo json_encode([
            "status" => "SECRET_KEY invalid",
            "code" => 401,
            "metadata" => null
        ]);
        exit;
    }

    // Jika valid → buat token
    $api_token          = generateRandomString(36);
    $datetime_creat     = date("Y-m-d H:i:s");
    $datetime_expired   = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Simpan token ke tabel api_token
    $id_account     = $row['id_account'];
    $sql_insert     = "INSERT INTO api_token (id_account, api_token, datetime_creat, datetime_expired) VALUES (?, ?, ?, ?)";
    $stmt_insert    = $Conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $id_account, $api_token, $datetime_creat, $datetime_expired);

    if ($stmt_insert->execute()) {
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (10 * 60)));
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header('Content-Type: application/json');
		header('Pragma: no-cache');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Credentials: true');
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
		header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, x-token, token"); 
        echo json_encode([
            "status" => "success",
            "code" => 200,
            "metadata" => [
                "x-token" => $api_token,
                "datetime_creat" => $datetime_creat,
                "datetime_expired" => $datetime_expired
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "Failed to save token $id_account",
            "code" => 500,
            "metadata" => null
        ]);
    }
?>
