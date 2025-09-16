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
			if (empty($Tangkap['setting']['urll_call_back'])) {
				$urll_call_back ="";
			}else{
				$urll_call_back = $Tangkap['setting']['urll_call_back'];
			}
			if (empty($Tangkap['setting']['id_marchant'])) {
				$Array['status'] = "ID Marchant Tidak Boleh Kosong";
				$Array['code'] = 401;
				sendResponse($Array);
			} else {
				if (empty($Tangkap['setting']['client_key'])) {
					$Array['status'] = "Client Key Tidak Boleh Kosong";
					$Array['code'] = 401;
					sendResponse($Array);
				} else {
					if (empty($Tangkap['setting']['server_key'])) {
						$Array['status'] = "Server Key Tidak Boleh Kosong";
						$Array['code'] = 401;
						sendResponse($Array);
					} else {
						if (empty($Tangkap['setting']['snap_url'])) {
							$Array['status'] = "Snap URL Tidak Boleh Kosong";
							$Array['code'] = 401;
							sendResponse($Array);
						} else {
							if (empty($Tangkap['setting']['production'])) {
								$Array['status'] = "Informasi Envi Tidak Boleh Kosong";
								$Array['code'] = 401;
								sendResponse($Array);
							} else {
						    	if (empty($Tangkap['setting']['url_status'])) {
    								$Array['status'] = "URL Status Tidak Boleh Kosong";
    								$Array['code'] = 401;
    								sendResponse($Array);
    							} else {
    								// Jika API key valid, ambil pengaturan dari database
    								$id_marchant = $Tangkap['setting']['id_marchant'];
    								$client_key = $Tangkap['setting']['client_key'];
    								$server_key = $Tangkap['setting']['server_key'];
    								$snap_url = $Tangkap['setting']['snap_url'];
    								$production = $Tangkap['setting']['production'];
    								$url_status = $Tangkap['setting']['url_status'];
    								//Bersihkan Variabel
    								$urll_call_back = validateAndSanitizeInput($urll_call_back);
    								$id_marchant = validateAndSanitizeInput($id_marchant);
    								$client_key = validateAndSanitizeInput($client_key);
    								$server_key = validateAndSanitizeInput($server_key);
    								$snap_url = validateAndSanitizeInput($snap_url);
    								$production = validateAndSanitizeInput($production);
    								//Update Data
    								$UpdateSetting= mysqli_query($Conn,"UPDATE setting_payment SET 
    									urll_call_back='$urll_call_back',
    									url_status='$url_status',
    									id_marchant='$id_marchant',
    									client_key='$client_key',
    									server_key='$server_key',
    									snap_url='$snap_url',
    									production='$production'
    								WHERE api_key='$api_key_client'") or die(mysqli_error($Conn)); 
    								if($UpdateSetting){
    									$Array['status'] = "Success";
    									$Array['code'] = 200;
    									sendResponse($Array);
    								}else{
    									$Array['status'] = "Terjadi kesalahan pada saat update setting";
    									$Array['code'] = 401;
    									sendResponse($Array);
    								}
    							}
							}
						}
					}
				}
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
