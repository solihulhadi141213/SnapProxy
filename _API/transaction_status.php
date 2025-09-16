<?php
    include "_Config/Connection.php";
    include "_Config/Function.php";
	// Membaca input JSON dan validasi
	$fp = fopen('php://input', 'r');
	$raw = stream_get_contents($fp);
	// Tambahkan batasan ukuran input sebelum memproses JSON
    if (strlen($raw) > 5000) { // Contoh batasan 5000 karakter
        $Array['status'] = "Payload too large";
        $Array['code'] = 413; // HTTP status 413 Payload Too Large
        sendResponse($Array);
    }
    $Tangkap = json_decode($raw, true);
    // Periksa kesalahan saat decoding JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        $Array['status'] = "Invalid JSON format";
        $Array['code'] = 400; // Bad Request
        sendResponse($Array);
    }
    // Tetap lanjutkan dengan validasi
    if (empty($Tangkap) || !is_array($Tangkap)) {
        $Array['status'] = "Invalid JSON format";
        $Array['code'] = 400;
        sendResponse($Array);
    }
    // Default response
	$Array = Array (
		"status" => "error",
		"code" => 400
	);
    // Membuka API Key dari database (asli)
    $api_key_database = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'api_key');
	// Validasi apakah API key disediakan
	if (empty($Tangkap['api_key'])) {
		$Array['status'] = "API Key Tidak Boleh Kosong";
		$Array['code'] = 401;
		sendResponse($Array);
	} else {
        if (empty($Tangkap['order_id'])) {
            $Array['status'] = "Order ID Tidak Boleh Kosong";
            $Array['code'] = 401;
            sendResponse($Array);
        } else {
            // Ambil API key dari request dan sanitasi input
            $api_key_client = validateAndSanitizeInput($Tangkap['api_key']);
            $order_id = validateAndSanitizeInput($Tangkap['order_id']);
            // Validasi API key menggunakan prepared statement
            $stmt = $Conn->prepare("SELECT * FROM setting_payment WHERE api_key = ?");
            $stmt->bind_param("s", $api_key_client);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                // Jika API key tidak valid
                $Array['status'] = "API Key Akses Payment Gateway Tidak Valid!";
                $Array['code'] = 401;
            } else {
                //Kirim Data Ke Midtrans
                $url_status = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'url_status');
                $server_key = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'server_key');
                $server_key_base64=base64_encode($server_key);
                //Open Service
                $curl2 = curl_init();
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => ''.$url_status.'/'.$order_id.'/status',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Accept:  application/json',
                        'Content-Type: application/json',
                        'Authorization: Basic '.$server_key_base64.''
                    ),
                ));
                $response2 = curl_exec($curl2);
                $array_response2=json_decode($response2, true);
                $Array['status'] = "Success";
                $Array['code'] = 200;
                $Array['response'] = $array_response2;
                sendResponse($Array);
            }
        }
		// Tutup statement
		$stmt->close();
	}
	// Fungsi untuk mengirimkan respons JSON
	function sendResponse($response) {
		header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (10 * 60)));
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header('Content-Type: application/json');
		header('Pragma: no-cache');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Credentials: true');
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
		header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, x-token, token"); 
		// JSON Response
		echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		exit();
	}
	// Kirim response akhir
	sendResponse($Array);
?>