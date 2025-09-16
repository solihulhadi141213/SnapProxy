<?php
    include "_Config/Connection.php";
    include "_Config/Function.php";
	
	// Membaca input JSON dan validasi
	$fp = fopen('php://input', 'r');
	$raw = stream_get_contents($fp);
	$Tangkap = json_decode($raw, true);

    // Default response
	$Array = Array (
		"status" => "error",
		"code" => 400
	);

    // Mencegah pengiriman data kosong atau invalid
    if (empty($Tangkap) || !is_array($Tangkap)) {
        $Array['status'] = "Invalid JSON format";
        $Array['code'] = 400;
        sendResponse($Array);
    }

    // Membuka API Key dari database (asli)
    $api_key_database = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'api_key');
	
	// Validasi apakah API key disediakan
	if (empty($Tangkap['api_key'])) {
		$Array['status'] = "API Key Tidak Boleh Kosong";
		$Array['code'] = 401;
		sendResponse($Array);
	} else {
        // Ambil API key dari request dan sanitasi input
        $api_key_client = validateAndSanitizeInput($Tangkap['api_key']);

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
            // Jika API key valid, ambil pengaturan dari database
            $urll_call_back = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'urll_call_back');
            $id_marchant = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'id_marchant');
            $client_key = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'client_key');
            $server_key = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'server_key');
            $snap_url = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'snap_url');
            $production = getDataDetail($Conn, 'setting_payment', 'id_setting_payment', '1', 'production');

            // Buat array pengaturan
            $Setting = Array(
                "urll_call_back" => $urll_call_back,
                "id_marchant" => $id_marchant,
                "client_key" => $client_key,
                "server_key" => $server_key,
                "snap_url" => $snap_url,
                "production" => $production
            );

            $Array['status'] = "Success";
            $Array['code'] = 200;
            $Array['setting'] = $Setting;
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
