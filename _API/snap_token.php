<?php
    header('Content-Type: application/json; charset=utf-8');

    //Koneksi
    include "../_Config/Connection.php";
    include "../_Config/Function.php";

    // Hanya boleh POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "Invalid request method", "code" => 405, "metadata" => null]);
        exit;
    }

    // Ambil header x-token (case-insensitive)
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $x_token = null;
    foreach ($headers as $k => $v) {
        if (strtolower($k) === 'x-token') {
            $x_token = trim($v);
            break;
        }
    }
    if (!$x_token) {
        echo json_encode(["status" => "x-token required", "code" => 401, "metadata" => null]);
        exit;
    }

    // Validasi x-token di db
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

    //Validasi kelengkapan data
    if(empty($data['kode_transaksi'])){
        echo json_encode(["status" => "Kode transaksi tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['gross_amount'])){
        echo json_encode(["status" => "Jumlah transaksi tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['name'])){
        echo json_encode(["status" => "Nama customer tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['email'])){
        echo json_encode(["status" => "Email customer tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['phone'])){
        echo json_encode(["status" => "Kontak customer tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }

    //Validasi Apakah Ada Setting Yang Aktif
    $id_setting_payment = getDataDetail($Conn,'setting_payment','status','active','id_setting_payment');
    if(empty($id_setting_payment)){
        echo json_encode(["status" => "Tidak ada pengaturan yang aktif", "code" => 400, "metadata" => null]);
        exit;
    }

    // Ambil variabel & sanitasi
    $id_transaction     = generate_uuid();
    $kode_transaksi     = validateAndSanitizeInput($data['kode_transaksi'] ?? "");
    $order_id           = generateRandomString(32); // Generate Order ID baru
    $datetime           = date('Y-m-d H:i:s'); //datetime sekarang (lebih lengkap)
    $id_marchant        = getDataDetail($Conn,'setting_payment','status','active','id_marchant');
    $server_key         = getDataDetail($Conn,'setting_payment','status','active','server_key');
    $client_key         = getDataDetail($Conn,'setting_payment','status','active','client_key');
    $production         = getDataDetail($Conn,'setting_payment','status','active','production');
    $gross_amount       = (int) round(floatval($data['gross_amount'] ?? 0)); // pastikan integer
    $name               = validateAndSanitizeInput($data['name'] ?? "");
    $email              = validateAndSanitizeInput($data['email'] ?? "");
    $phone              = validateAndSanitizeInput($data['phone'] ?? "");

    // ======= PENTING: Bersihkan API keys dari whitespace (spasi/newline/tab) =======
    $server_key = is_null($server_key) ? "" : preg_replace('/\s+/', '', trim($server_key));
    $client_key = is_null($client_key) ? "" : preg_replace('/\s+/', '', trim($client_key));

    if ($server_key === "") {
        echo json_encode(["status" => "Server key kosong atau tidak valid", "code" => 500, "metadata" => null]);
        exit;
    }

    // Pecah string berdasarkan spasi untuk nama
    $parts = explode(" ", trim($name));
    if (count($parts) == 1) {
        $first_name = $parts[0];
        $last_name = "";
    } else {
        $first_name = $parts[0];
        $last_name = implode(" ", array_slice($parts, 1));
    }

    //Include Library
    require_once "../midtrans-php-master/Midtrans.php";

    // Set your Merchant Server Key (pakai nilai yang sudah dibersihkan)
    \Midtrans\Config::$serverKey = $server_key;

    // (opsional) set clientKey juga jika diperlukan oleh library atau debug
    if ($client_key !== "") {
        // beberapa versi library mempunyai property clientKey; jika tidak ada, baris ini aman karena hanya assignment
        \Midtrans\Config::$clientKey = $client_key;
    }

    // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
    \Midtrans\Config::$isProduction = ($production === "true");

    // Set sanitization on (default)
    \Midtrans\Config::$isSanitized = true;

    // Set 3DS transaction for credit card to true
    \Midtrans\Config::$is3ds = true;

    $params = array(
        'transaction_details' => array(
            'order_id' => $order_id,
            'gross_amount' => $gross_amount,
        ),
        'customer_details' => array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
        ),
    );

    // Tangani exception agar response lebih rapi bila Midtrans error
    try {
        $snapToken = \Midtrans\Snap::getSnapToken($params);
    } catch (Exception $e) {
        echo json_encode(["status" => "Midtrans error: " . $e->getMessage(), "code" => 500, "metadata" => null]);
        exit;
    }

    if(!empty($snapToken)){
        //Bungkus Data
        $log = Array (
            "id_transaction" => $id_transaction,
            "kode_transaksi" => $kode_transaksi,
            "order_id" => $order_id,
            "datetime" => $datetime,
            "ServerKey" => $server_key,
            "Production" => $production,
            "gross_amount" => $gross_amount,
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "snapToken" => $snapToken
        );

        //Cek apakah kombinasi kode_transaksi dan order_id sudah ada (sebaiknya gunakan prepared statement)
        $QryOrder = mysqli_query($Conn,"SELECT * FROM transaction WHERE order_id='". mysqli_real_escape_string($Conn,$order_id) ."' AND kode_transaksi='". mysqli_real_escape_string($Conn,$kode_transaksi) ."'") or die(mysqli_error($Conn));
        $DataOrder = mysqli_fetch_array($QryOrder);
        if(empty($DataOrder['id_transaction'])){
            //Jika Tidak Ada Maka Insert
            $simpan=InsertKodeTransaksi($Conn,$log);
        }else{
            //Jika Ada Maka Update
            $simpan=UpdateKodeTransaksi($Conn,$log);
        }
        if($simpan!=="Berhasil"){
            echo json_encode(["status" => "Terjadi kesalahan pada saat menyimpan data transaksi : $simpan", "code" => 500, "metadata" => null]);
            exit;
        }else{
            $metadata=[
                "snap-token"=>$snapToken,
                "kode_transaksi"=>$kode_transaksi,
                "order_id"=>$order_id,
                "datetime"=>$datetime,
                "server_key"=>$server_key,
                "production"=>$production,
            ];
            echo json_encode(["status" => "success", "code" => 200, "metadata" => $metadata]);
            exit;
        }
    } else {
        echo json_encode(["status" => "Snap Token Gagal Dibuat", "code" => 500, "metadata" => null]);
        exit;
    }
?>
