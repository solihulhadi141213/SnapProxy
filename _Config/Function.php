<?php
    //Memanggil Detail Data
    function getDataDetail($Conn,$NamaDb,$NamaParam,$IdParam,$Kolom){
         // Validasi input yang diperlukan
        if (empty($Conn)) {
            return "No Database Connection";
        }
        if (empty($NamaDb)) {
            return "No Table Selected";
        }
        if (empty($NamaParam)) {
            return "No Parameter Selected";
        }
        if (empty($Value)) {
            return "No Value Provided";
        }
        if (empty($Kolom)) {
            return "No Column Selected";
        }
    
        // Escape table name and column name untuk mencegah SQL Injection
        $NamaDb = mysqli_real_escape_string($Conn, $NamaDb);
        $NamaParam = mysqli_real_escape_string($Conn, $NamaParam);
        $Kolom = mysqli_real_escape_string($Conn, $Kolom);
    
        // Menggunakan prepared statement
        $Qry = $Conn->prepare("SELECT $Kolom FROM $NamaDb WHERE $NamaParam = ?");
        if ($Qry === false) {
            return "Query Preparation Failed: " . $Conn->error;
        }
    
        // Bind parameter
        $Qry->bind_param("s", $Value);
    
        // Eksekusi query
        if (!$Qry->execute()) {
            return "Query Execution Failed: " . $Qry->error;
        }
    
        // Mengambil hasil
        $Result = $Qry->get_result();
        $Data = $Result->fetch_assoc();
    
        // Menutup statement
        $Qry->close();
    
        // Mengembalikan hasil
        if (empty($Data[$Kolom])) {
            return "";
        } else {
            return $Data[$Kolom];
        }
    }

    //Membersihkan karakter
    function validateAndSanitizeInput($input) {
        // Menghapus karakter yang tidak diinginkan
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    function generate_uuid() {
        $data = random_bytes(16);

        // Atur versi ke 0100
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        // Atur variant ke 10xx
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    //Membuat Token
    function generateRandomString($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $charLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charLength - 1)];
        }
        return $randomString;
    }
    function InsertKodeTransaksi($Conn, $log) {
        // Mendapatkan nilai dari array $log
        $id_transaction = $log['id_transaction'];
        $kode_transaksi = $log['kode_transaksi'];
        $order_id       = $log['order_id'];
        $datetime       = $log['datetime'];
        $ServerKey      = $log['ServerKey'];
        $Production     = $log['Production'];
        $gross_amount   = $log['gross_amount'];
        $name           = $log['name'];
        $email          = $log['email'];
        $phone          = $log['phone'];
        $snapToken      = $log['snapToken'];
        
        // Menyiapkan query menggunakan prepared statement
        $stmt = $Conn->prepare("INSERT INTO transaction (
            id_transaction,
            kode_transaksi,
            order_id,
            datetime,
            ServerKey,
            Production,
            gross_amount,
            name,
            email,
            phone,
            snapToken
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
        // Bind parameter untuk menghindari SQL Injection
        $stmt->bind_param(
            "ssssssdssss",  // s = string, d = double (gross_amount di sini dianggap sebagai decimal/float)
            $id_transaction, 
            $kode_transaksi, 
            $order_id, 
            $datetime, 
            $ServerKey, 
            $Production, 
            $gross_amount, 
            $name, 
            $email, 
            $phone, 
            $snapToken
        );
    
        // Eksekusi query dan cek hasilnya
        if ($stmt->execute()) {
            $Response = "Berhasil";
        } else {
            $Response = "Input Data Gagal: " . $stmt->error; // Untuk debug error
        }
    
        // Menutup prepared statement
        $stmt->close();
    
        return $Response;
    }
    
    function UpdateKodeTransaksi($Conn, $log){
        // Mendapatkan nilai dari array $log
        $id_transaction = $log['id_transaction'];
        $kode_transaksi = $log['kode_transaksi'];
        $order_id       = $log['order_id'];
        $datetime       = $log['datetime'];
        $ServerKey      = $log['ServerKey'];
        $Production     = $log['Production'];
        $gross_amount   = $log['gross_amount'];
        $name           = $log['name'];
        $email          = $log['email'];
        $phone          = $log['phone'];
        $snapToken      = $log['snapToken'];
        
        // Menggabungkan first name dan last name
        $name = "$first_name $last_name";

        //Melakukan Update
        $query = "UPDATE transaction SET 
            kode_transaksi='$kode_transaksi',
            order_id='$order_id',
            datetime='$datetime',
            ServerKey='$ServerKey',
            Production='$Production',
            gross_amount='$gross_amount',
            name='$name',
            email='$email',
            phone='$phone',
            snapToken='$snapToken'
        WHERE id_transaction='$id_transaction'";
        $UpdateOrderId = mysqli_query($Conn, $query);
        if ($UpdateOrderId) {
            $Response = "Berhasil";
        } else {
            $Response = "Update Data Gagal: " . mysqli_error($Conn); 
            // Menggabungkan pesan error dari MySQL
        }
        return $Response;
    }
    
    //Delete Data
    function DeleteData($Conn,$NamaDb,$NamaParam,$IdParam){
        $HapusData = mysqli_query($Conn, "DELETE FROM $NamaDb WHERE $NamaParam='$IdParam'") or die(mysqli_error($Conn));
        if($HapusData){
            $Response="Success";
        }else{
            $Response="Hapus Data Gagal";
        }
        return $Response;
    }
    //Update Status Transaksi Admin
    function UpdateStatusTransaksiAdmin($api_key,$AdminBaseUrl,$kode_transaksi,$status){
       //Krim data dengan CURL
        $headers = array(
            'Content-Type:Application/x-www-form-urlencoded',         
        );
        //CURL send data
        $arr = array(
            "api_key" => "$api_key",
            "kode_transaksi" => "$kode_transaksi",
            "status" => "$status"
        );
        $json = json_encode($arr);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "$AdminBaseUrl/_API/CallBack/UpdateStatusPembayaran.php");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $data =json_decode($response, true);
        $Response=$data;
        return $Response;
    }
    // Fungsi sederhana validasi URL
    function isValidUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    function insertLog($Conn, $id_setting_payment, $order_id, $datetime, $status) {
        
        // Menyiapkan query menggunakan prepared statement
        $stmt = $Conn->prepare("INSERT INTO log_notification (
            id_setting_payment,
            order_id,
            datetime,
            status
        ) VALUES (?, ?, ?, ?)");
    
        // Bind parameter untuk menghindari SQL Injection
        $stmt->bind_param(
            "isss",  // s = string, d = double (gross_amount di sini dianggap sebagai decimal/float)
            $id_setting_payment, 
            $order_id, 
            $datetime, 
            $status
        );
    
        // Eksekusi query dan cek hasilnya
        if ($stmt->execute()) {
            $Response = "Berhasil";
        } else {
            $Response = "Input Data Gagal: " . $stmt->error; // Untuk debug error
        }
    
        // Menutup prepared statement
        $stmt->close();
    
        return $Response;
    }
?>