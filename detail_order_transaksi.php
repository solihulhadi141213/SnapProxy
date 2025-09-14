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
            //id_order_transaksi tidak boleh kosong
            if(empty($Tangkap['id_order_transaksi'])){
                // Jika API key tidak valid
                $Array['status'] = "id_order_transaksi tidak boleh kosong";
                $Array['code'] = 401;
			}else{
                $id_order_transaksi=$Tangkap['id_order_transaksi'];
                //Bersihkan Variabel
                $id_order_transaksi = validateAndSanitizeInput($id_order_transaksi);
                //Cari Data
                $id_order_transaksi=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'id_order_transaksi');
                if(empty($id_order_transaksi)){
                    // Jika API key tidak valid
                    $Array['status'] = "id_order_transaksi tidak valid karena tidak ditemukan pada database";
                    $Array['code'] = 401;
                }else{
                    $kode_transaksi=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'kode_transaksi');
                    $order_id=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'order_id');
                    $datetime=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'datetime');
                    $ServerKey=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'ServerKey');
                    $Production=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'Production');
                    $gross_amount=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'gross_amount');
                    $name=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'name');
                    $email=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'email');
                    $phone=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'phone');
                    $snapToken=getDataDetail($Conn,'order_transaksi','id_order_transaksi',$id_order_transaksi,'snapToken');
                    //Menghitung Apakah Ada Log Payment
                    $jml_data_log_payment = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM log_payment WHERE order_id='$order_id'"));
                    //Apabila Tidak Ada Log Payment
                    if(empty($jml_data_log_payment)){
                        $log_payment=[];
                    }else{
                        $log_payment=array();
                        $sql = "SELECT * FROM log_payment WHERE order_id='$order_id' ORDER BY id_log_payment DESC";
                        $qry = mysqli_query($Conn, $sql);
                        while ($x = mysqli_fetch_array($qry)) {
                            $h['id_log_payment'] =$x["id_log_payment"];
                            $h['kode_transaksi'] =$x["kode_transaksi"];
                            $h['order_id'] =$x["order_id"];
                            $h['transaction_time'] =$x["transaction_time"];
                            $h['status_code'] =$x["status_code"];
                            $h['payment_type'] =$x["payment_type"];
                            $h['gross_amount'] =$x["gross_amount"];
                            $h['fraud_status'] =$x["fraud_status"];
                            $h['transaction_status'] =$x["transaction_status"];
                            array_push($log_payment, $h);
                        }
                    }
                    $detail = [
                        "id_order_transaksi" => $id_order_transaksi,
                        "kode_transaksi" => $kode_transaksi,
                        "order_id" => $order_id,
                        "datetime" => $datetime,
                        "ServerKey" => $ServerKey,
                        "Production" => $Production,
                        "gross_amount" => $gross_amount,
                        "name" => $name,
                        "email" => $email,
                        "phone" => $phone,
                        "snapToken" => $snapToken
                    ];
                    // Membuat Response
                    $Array = [
                        'status' => "success",
                        'code' => 200,
                        'detail' => $detail,
                        'log_payment' => $log_payment,
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