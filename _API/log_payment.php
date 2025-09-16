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
            //limit (batas jumlah data pada setiap halaman, apabila kosong maka akan bernilai 10)
            if(empty($Tangkap['filter']['limit'])){
                $limit="10";
			}else{
                $limit=$Tangkap['filter']['limit'];
            }
            //page (posisi halaman, apabila kosong maka akan bernilai 1)
            if(empty($Tangkap['filter']['page'])){
                $page="1";
                $posisi = 0;
			}else{
                $page=$Tangkap['filter']['page'];
                $posisi = ( $page - 1 ) * $limit;
            }
            //ShortBy (Dasar pengurutan data, apabila kosong maka akan bernilai id_log_payment)
            if(empty($Tangkap['filter']['ShortBy'])){
                $ShortBy="DESC";
			}else{
                $ShortBy=$Tangkap['filter']['ShortBy'];
            }
            //OrderBy (Mode pengurutan data, apabila kosong maka akan bernilai DESC)
            if(empty($Tangkap['filter']['OrderBy'])){
                $OrderBy="id_log_payment";
			}else{
                $OrderBy=$Tangkap['filter']['OrderBy'];
            }
            //keyword_by (Dasar atribut untuk pencarian, apabila kosong maka diartikan untuk menampilkan semua data)
			if(empty($Tangkap['filter']['keyword_by'])){
                $keyword_by="";
			}else{
                $keyword_by=$Tangkap['filter']['keyword_by'];
            }
            //keyword (Kata kunci pencarian, apabila kosong maka tidak akan dilakukan pencarian)
            if(empty($Tangkap['filter']['keyword'])){
                $keyword="";
			}else{
                $keyword=$Tangkap['filter']['keyword'];
            }
            //Bersihkan Variabel
            $page = validateAndSanitizeInput($page);
            $limit = validateAndSanitizeInput($limit);
            $ShortBy = validateAndSanitizeInput($ShortBy);
            $OrderBy = validateAndSanitizeInput($OrderBy);
            $keyword_by = validateAndSanitizeInput($keyword_by);
            $keyword = validateAndSanitizeInput($keyword);
            //Maksimal data yang ditampilkan adalah 100
            if($limit>100){
                $Array['status'] = "Nilai limit data yang ditampilkan maksimal 100";
                $Array['code'] = 401;
                sendResponse($Array);
            }else{
                //ShortBy hanya boleh bernilai ASC atau DESC
                if($ShortBy!=='ASC'&&$ShortBy!=='DESC'){
                    $Array['status'] = "Dasar Pengurutan Data Hanya Boleh ASC (Ascending) atau DESC (Descanding)";
                    $Array['code'] = 401;
                    sendResponse($Array);
                }else{
                    //$OrderBy hanya boleh sesuai atribut
                    $allowedColumns = [
                        'id_log_payment', 
                        'kode_transaksi', 
                        'order_id', 
                        'transaction_time', 
                        'status_code', 
                        'payment_type', 
                        'gross_amount', 
                        'fraud_status', 
                        'transaction_status'
                    ];
                    if (!in_array($OrderBy, $allowedColumns)) {
                        $Array['status'] = "Dasar pengurutan data tidak valid. Anda hanya boleh mengurutkan data berdasarkan atribut yang ada";
                        $Array['code'] = 401;
                        sendResponse($Array);
                    }else{
                        //keyword_by hanya boleh berdasarkan kolom
                        if (!in_array($keyword_by, $allowedColumns)) {
                            $Array['status'] = "Dasar Pencarian Tidak Valid";
                            $Array['code'] = 401;
                            sendResponse($Array);
                        }else{
                            //Jumlah Data
                            if(empty($keyword_by)){
                                if(empty($keyword)){
                                    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM log_payment"));
                                }else{
                                    // Variabel pencarian
                                    $keyword = mysqli_real_escape_string($Conn, $keyword);
                                    // Bangun query dinamis berdasarkan kolom
                                    $whereClauses = [];
                                    foreach ($allowedColumns as $column) {
                                        $whereClauses[] = "$column LIKE '%$keyword%'";
                                    }
                                    // Gabungkan semua kondisi dengan OR
                                    $whereSql = implode(' OR ', $whereClauses);
                                    // Query untuk menghitung jumlah data
                                    $sql = "SELECT COUNT(*) as total FROM log_payment WHERE $whereSql";
                                    // Eksekusi query
                                    $result = mysqli_query($Conn, $sql);
                                    // Ambil jumlah data
                                    $row = mysqli_fetch_assoc($result);
                                    $jml_data = $row['total'];
                                }
                            }else{
                                if(empty($keyword)){
                                    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM log_payment"));
                                }else{
                                    $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM log_payment WHERE $keyword_by like '%$keyword%'"));
                                }
                            }
                            if(empty($jml_data)){
                                $Array['status'] = "Data Log Payment Tidak Ditemukan";
                                $Array['code'] = 401;
                                sendResponse($Array);
                            }else{
                                $JmlHalaman = ceil($jml_data/$limit);
                                if($page>$JmlHalaman){
                                    $Array['status'] = "Halaman yang anda masukan melebihi batas!";
                                    $Array['code'] = 401;
                                    sendResponse($Array);
                                }else{
                                    //Buka Data
                                    $list = array();
                                    //Amankan variabel
                                    $keyword = mysqli_real_escape_string($Conn, $keyword);
                                    $OrderBy = mysqli_real_escape_string($Conn, $OrderBy);
                                    $ShortBy = mysqli_real_escape_string($Conn, $ShortBy);
                                    $keyword_by = mysqli_real_escape_string($Conn, $keyword_by);
                                    // Default query tanpa filter keyword
                                    $sql = "SELECT * FROM log_payment ORDER BY $OrderBy $ShortBy LIMIT $posisi, $limit";
                                    // Jika ada pencarian keyword
                                    if (!empty($keyword)) {
                                        if (empty($keyword_by)) {
                                            // Jika tidak ada kolom tertentu untuk pencarian, lakukan pencarian di beberapa kolom
                                            $allowedColumns = ['kode_transaksi', 'order_id']; // Tambahkan kolom lain sesuai kebutuhan
                                            $whereClauses = [];
                                            // Buat query pencarian dinamis berdasarkan kolom yang ada
                                            foreach ($allowedColumns as $column) {
                                                $whereClauses[] = "$column LIKE '%$keyword%'";
                                            }
                                            // Gabungkan semua kondisi pencarian dengan OR
                                            $whereSql = implode(' OR ', $whereClauses);
                                            // Query untuk pencarian di beberapa kolom
                                            $sql = "SELECT * FROM log_payment WHERE $whereSql ORDER BY $OrderBy $ShortBy LIMIT $posisi, $limit";
                                        } else {
                                            // Jika ada kolom tertentu untuk pencarian
                                            $sql = "SELECT * FROM log_payment WHERE $keyword_by LIKE '%$keyword%' ORDER BY $OrderBy $ShortBy LIMIT $posisi, $limit";
                                        }
                                    }
                                    // Eksekusi query
                                    $QryPayment = mysqli_query($Conn, $sql);
                                    // Fetch data
                                    while ($x = mysqli_fetch_array($QryPayment)) {
                                        $id_log_payment = $x["id_log_payment"];
                                        $kode_transaksi=$x["kode_transaksi"];
                                        $order_id=$x["order_id"];
                                        $transaction_time=$x["transaction_time"];
                                        $status_code=$x["status_code"];
                                        $payment_type=$x["payment_type"];
                                        $gross_amount=$x["gross_amount"];
                                        $fraud_status=$x["fraud_status"];
                                        $transaction_status=$x["transaction_status"];
                                        //Buat Dalam Bentuk Array
                                        $h['id_log_payment'] =$id_log_payment ;
                                        $h['kode_transaksi'] =$kode_transaksi ;
                                        $h['order_id'] =$order_id ;
                                        $h['transaction_time'] =$transaction_time ;
                                        $h['status_code'] =$status_code ;
                                        $h['payment_type'] =$payment_type ;
                                        $h['gross_amount'] =$gross_amount ;
                                        $h['fraud_status'] =$fraud_status ;
                                        $h['transaction_status'] =$transaction_status ;
                                        array_push($list, $h);
                                    }
                                    $Array['status'] = "success";
                                    $Array['code'] = 200;
                                    $Array['jumlah_data'] = $jml_data;
                                    $Array['jumlah_halaman'] = $JmlHalaman;
                                    $Array['list'] = $list;
                                    sendResponse($Array);
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