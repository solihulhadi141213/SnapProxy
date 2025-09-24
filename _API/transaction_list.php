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
    $stmt->close();

    //Variabel id_account
    $id_account = $row['id_account'];

    // Ambil body JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    if (!$data) {
        echo json_encode(["status" => "Invalid JSON", "code" => 400, "metadata" => null]);
        exit;
    }

    //Validasi kelengkapan data
    if(empty($data['limit'])){
        echo json_encode(["status" => "Limit/Batas jumlah data tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['page'])){
        echo json_encode(["status" => "Posisi halaman tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['short_by'])){
        echo json_encode(["status" => "Mode urutan data (Short By) tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }
    if(empty($data['order_by'])){
        echo json_encode(["status" => "Dasar urutan data (Order By) tidak boleh kosong", "code" => 400, "metadata" => null]);
        exit;
    }

    //Buat Variabel
    $limit      = validateAndSanitizeInput($data['limit'] ?? 10);
    $page       = validateAndSanitizeInput($data['page'] ?? 1);
    $short_by   = validateAndSanitizeInput($data['short_by'] ?? "DESC");
    $order_by   = validateAndSanitizeInput($data['order_by'] ?? "datetime");

    //Variabel yang tidak wajib
    if(empty($data['keyword_by'])){
        $keyword_by="";
    }else{
        $keyword_by=$data['keyword_by'];
    }
    if(empty($data['keyword'])){
        $keyword="";
    }else{
        $keyword=$data['keyword'];
    }

    //Batasi limit/data yang ditampilkan
    if($limit>100){
        echo json_encode(["status" => "Batas limit data yang ditampilkan maksimal 100", "code" => 401, "metadata" => null]);
        exit;
    }

    //short_by hanya boleh bernilai ASC atau DESC
    if($short_by!=='ASC'&&$short_by!=='DESC'){
        echo json_encode(["status" => "Dasar Pengurutan Data Hanya Boleh ASC (Ascending) atau DESC (Descanding)", "code" => 401, "metadata" => null]);
        exit;
    }

    //Validasi Apakah Ada Setting Yang Aktif
    $id_setting_payment = getDataDetail($Conn,'setting_payment','status','active','id_setting_payment');
    if(empty($id_setting_payment)){
        echo json_encode(["status" => "Tidak ada pengaturan yang aktif", "code" => 400, "metadata" => null]);
        exit;
    }

    //Daftar Kolom Pada Database
    $allowedColumns = [
        'id_transaction', 
        'id_setting_payment', 
        'kode_transaksi', 
        'order_id', 
        'datetime', 
        'ServerKey', 
        'Production', 
        'gross_amount', 
        'name', 
        'email', 
        'phone', 
        'snapToken'
    ];

    //Validasi order_by
    if (!in_array($order_by, $allowedColumns)) {
        echo json_encode(["status" => "Dasar pengurutan data tidak valid. Anda hanya boleh mengurutkan data berdasarkan atribut yang ada", "code" => 401, "metadata" => null]);
        exit;
    }

    //Validasi keyword_by
    if (!in_array($keyword_by, $allowedColumns) && $keyword_by !== '') {
        $Notifikasi = "Dasar Pencarian Tidak Valid (Hanya Boleh " . implode(' atau ', ['kode_transaksi', 'order_id']) . ")";
        echo json_encode(["status" => "$Notifikasi", "code" => 401, "metadata" => null]);
        exit;
    }

    //Menghitung Jumlah Data
    if(empty($keyword_by)){
        if(empty($keyword)){
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM transaction WHERE id_setting_payment='$id_setting_payment'"));
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
            $sql = "SELECT COUNT(*) as total FROM transaction WHERE (id_setting_payment='$id_setting_payment') AND ($whereSql)";
            // Eksekusi query
            $result = mysqli_query($Conn, $sql);
            // Ambil jumlah data
            $row = mysqli_fetch_assoc($result);
            $jml_data = $row['total'];
        }
    }else{
        if(empty($keyword)){
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM transaction WHERE id_setting_payment='$id_setting_payment'"));
        }else{
            $jml_data = mysqli_num_rows(mysqli_query($Conn, "SELECT*FROM transaction WHERE (id_setting_payment='$id_setting_payment') AND ($keyword_by like '%$keyword%')"));
        }
    }
    if(empty($jml_data)){
        echo json_encode(["status" => "Tidak Ada Data Transaksi Yang Ditampilkan", "code" => 201, "metadata" => null]);
        exit;
    }

    //Hitung Jumlah Halaman
    $page_count = ceil($jml_data/$limit);
    if($page>$page_count){
        echo json_encode(["status" => "Halaman Melebihi Batas", "code" => 401, "metadata" => null]);
        exit;
    }

    //Tetapkan Posisi
    $posisi = ( $page - 1 ) * $limit;
    
    //Amankan variabel parameter filter dengan 'mysqli_real_escape_string'
    $keyword    = mysqli_real_escape_string($Conn, $keyword);
    $OrderBy    = mysqli_real_escape_string($Conn, $order_by);
    $ShortBy    = mysqli_real_escape_string($Conn, $short_by);
    $keyword_by = mysqli_real_escape_string($Conn, $keyword_by);
    
    // Default query tanpa filter keyword
    $sql = "SELECT * FROM transaction WHERE id_setting_payment='$id_setting_payment' ORDER BY $OrderBy $ShortBy LIMIT $posisi, $limit";
    
    // Jika ada pencarian keyword
    if (!empty($keyword)) {
        if (empty($keyword_by)) {
            
            // Jika tidak ada kolom tertentu untuk pencarian, lakukan pencarian di beberapa kolom
            $whereClauses = [];
            
            // Buat query pencarian dinamis berdasarkan kolom yang ada
            foreach ($allowedColumns as $column) {
                $whereClauses[] = "$column LIKE '%$keyword%'";
            }
            
            // Gabungkan semua kondisi pencarian dengan OR
            $whereSql = implode(' OR ', $whereClauses);
            
            // Query untuk pencarian di beberapa kolom
            $sql = "SELECT * FROM transaction WHERE (id_setting_payment='$id_setting_payment') AND ($whereSql) ORDER BY $OrderBy $ShortBy LIMIT $posisi, $limit";
        } else {
            
            // Jika ada kolom tertentu untuk pencarian
            $sql = "SELECT * FROM transaction WHERE (id_setting_payment='$id_setting_payment') AND ($keyword_by LIKE '%$keyword%') ORDER BY $OrderBy $ShortBy LIMIT $posisi, $limit";
        }
    }
    
    // Eksekusi query
    $QryPayment = mysqli_query($Conn, $sql);
    
    // Fetch data
    $list       = array();
    while ($x   = mysqli_fetch_array($QryPayment)) {
        $h['id_transaction']        = $x["id_transaction"];
        $h['id_setting_payment']    = $x["id_setting_payment"];
        $h['kode_transaksi']        = $x["kode_transaksi"];
        $h['order_id']              = $x["order_id"];
        $h['datetime']              = $x["datetime"];
        $h['ServerKey']             = $x["ServerKey"];
        $h['Production']            = $x["Production"];
        $h['gross_amount']          = $x["gross_amount"];
        $h['name']                  = $x["name"];
        $h['email']                 = $x["email"];
        $h['phone']                 = $x["phone"];
        $h['snapToken']             = $x["snapToken"];
        array_push($list, $h);
    }
    $metadata['data_count'] = $jml_data;
    $metadata['page_count'] = $page_count;
    $metadata['current_page'] = $page;
    $metadata['list'] = $list;
    echo json_encode(["status" => "success", "code" => 200, "metadata" => $metadata]);
?>