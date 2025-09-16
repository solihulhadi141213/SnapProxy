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
            //id_log_payment tidak boleh kosong
            if(empty($Tangkap['id_log_payment'])){
                // Jika API key tidak valid
                $Array['status'] = "id_log_payment tidak boleh kosong";
                $Array['code'] = 401;
			}else{
                $id_log_payment=$Tangkap['id_log_payment'];
                //Bersihkan Variabel
                $id_log_payment = validateAndSanitizeInput($id_log_payment);
                //Cari Data
                $id_log_payment=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'id_log_payment');
                if(empty($id_log_payment)){
                    // Jika API key tidak valid
                    $Array['status'] = "id_log_payment tidak valid karena tidak ditemukan pada database";
                    $Array['code'] = 401;
                }else{
                    $kode_transaksi=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'kode_transaksi');
                    $order_id=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'order_id');
                    $transaction_time=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'transaction_time');
                    $status_code=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'status_code');
                    $payment_type=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'payment_type');
                    $gross_amount=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'gross_amount');
                    $fraud_status=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'fraud_status');
                    $transaction_status=getDataDetail($Conn,'log_payment','id_log_payment',$id_log_payment,'transaction_status');
                    $detail = [
                        "id_log_payment" => $id_log_payment,
                        "kode_transaksi" => $kode_transaksi,
                        "order_id" => $order_id,
                        "transaction_time" => $transaction_time,
                        "status_code" => $status_code,
                        "payment_type" => $payment_type,
                        "gross_amount" => $gross_amount,
                        "fraud_status" => $fraud_status,
                        "transaction_status" => $transaction_status
                    ];
                    
                    // Membuat Response
                    $Array = [
                        'status' => "success",
                        'code' => 200,
                        'detail' => $detail
                    ];
                    sendResponse($Array);
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