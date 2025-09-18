<?php
    header('Content-Type: application/json');
    include "../_Config/Connection.php";
    include "../_Config/Function.php";

    

    // Fungsi sanitasi input
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Hanya boleh POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "Invalid request method", "code" => 405, "metadata" => null]);
        exit;
    }

    // Validasi x-token
    $headers = getallheaders();
    if (!isset($headers['x-token'])) {
        echo json_encode(["status" => "x-token required", "code" => 401, "metadata" => null]);
        exit;
    }
    $x_token = trim($headers['x-token']);

    // Cek token di db
    $sql = "SELECT id_account, datetime_expired FROM api_token WHERE api_token = ?";
    $stmt = $Conn->prepare($sql);
    $stmt->bind_param("s", $x_token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["status" => "Invalid token", "code" => 401, "metadata" => null]);
        exit;
    }
    $row = $res->fetch_assoc();
    if (strtotime($row['datetime_expired']) < time()) {
        echo json_encode(["status" => "Token expired", "code" => 401, "metadata" => null]);
        exit;
    }
    $id_account = $row['id_account'];

    // Ambil body JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    if (!$data) {
        echo json_encode(["status" => "Invalid JSON", "code" => 400, "metadata" => null]);
        exit;
    }

    // Ambil variabel & sanitasi
    $env_name       = validateAndSanitizeInput($data['env_name'] ?? "");
    $urll_call_back = validateAndSanitizeInput($data['urll_call_back'] ?? "");
    $url_status     = validateAndSanitizeInput($data['url_status'] ?? "");
    $id_marchant    = validateAndSanitizeInput($data['id_marchant'] ?? "");
    $client_key     = validateAndSanitizeInput($data['client_key'] ?? "");
    $server_key     = validateAndSanitizeInput($data['server_key'] ?? "");
    $snap_url       = validateAndSanitizeInput($data['snap_url'] ?? "");
    $production     = validateAndSanitizeInput($data['production'] ?? "");
    $status         = validateAndSanitizeInput($data['status'] ?? "");

    // Validasi aturan
    if ($env_name == "") {
        echo json_encode(["status" => "env_name required", "code" => 400, "metadata" => null]); exit;
    }

    // env_name unik
    $q = $Conn->prepare("SELECT id_setting_payment FROM setting_payment WHERE id_account=? AND env_name=?");
    $q->bind_param("ss", $id_account, $env_name);
    $q->execute();
    $r = $q->get_result();
    if ($r->num_rows > 0) {
        echo json_encode(["status" => "env_name already exists", "code" => 400, "metadata" => null]); exit;
    }

    if ($urll_call_back != "" && !isValidUrl($urll_call_back)) {
        echo json_encode(["status" => "Invalid urll_call_back URL", "code" => 400, "metadata" => null]); exit;
    }
    if ($url_status != "" && !isValidUrl($url_status)) {
        echo json_encode(["status" => "Invalid url_status URL", "code" => 400, "metadata" => null]); exit;
    }
    if ($id_marchant == "") {
        echo json_encode(["status" => "id_marchant required", "code" => 400, "metadata" => null]); exit;
    }
    if ($client_key == "") {
        echo json_encode(["status" => "client_key required", "code" => 400, "metadata" => null]); exit;
    }
    if ($server_key == "") {
        echo json_encode(["status" => "server_key required", "code" => 400, "metadata" => null]); exit;
    }
    if ($snap_url == "" || !isValidUrl($snap_url)) {
        echo json_encode(["status" => "Invalid snap_url", "code" => 400, "metadata" => null]); exit;
    }
    if (!in_array($production, ["true","false"])) {
        echo json_encode(["status" => "production must be 'true' or 'false'", "code" => 400, "metadata" => null]); exit;
    }
    if (!in_array($status, ["active","none"])) {
        echo json_encode(["status" => "status must be 'active' or 'none'", "code" => 400, "metadata" => null]); exit;
    }

    // Jika status active → ubah setting lain jadi none
    if ($status == "active") {
        $Conn->query("UPDATE setting_payment SET status='none' WHERE id_account='".$Conn->real_escape_string($id_account)."'");
    }

    // Insert setting baru
    $sqlIns = "INSERT INTO setting_payment 
    (id_account, env_name, urll_call_back, url_status, id_marchant, client_key, server_key, snap_url, production, status) 
    VALUES (?,?,?,?,?,?,?,?,?,?)";
    $stmtIns = $Conn->prepare($sqlIns);
    $stmtIns->bind_param("ssssssssss", 
        $id_account, $env_name, $urll_call_back, $url_status, 
        $id_marchant, $client_key, $server_key, $snap_url, $production, $status
    );

    //Header
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (10 * 60)));
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header('Content-Type: application/json');
    header('Pragma: no-cache');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Credentials: true');
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, x-token, token"); 
    if ($stmtIns->execute()) {
        echo json_encode([
            "status" => "success",
            "code" => 200,
            "metadata" => [
                "env_name"       => $env_name,
                "urll_call_back" => $urll_call_back,
                "url_status"     => $url_status,
                "id_marchant"    => $id_marchant,
                "client_key"     => $client_key,
                "server_key"     => $server_key,
                "snap_url"       => $snap_url,
                "production"     => $production,
                "status"         => $status
            ]
        ]);
    } else {
        echo json_encode(["status" => "Failed to save setting", "code" => 500, "metadata" => null]);
    }
?>
