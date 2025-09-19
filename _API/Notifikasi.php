<?php
    //Koneksi
    include "_Config/Connection.php";
    include "_Config/Function.php";
    
    // Buka Pengaturan / Setting
    $QryParam = $Conn->prepare("SELECT * FROM setting_payment WHERE status = ?");
    $status = "active";
    $QryParam->bind_param('s', $status);
    $QryParam->execute();
    $ResultParam = $QryParam->get_result();
    $DataParam = $ResultParam->fetch_array();
    
    // Menutup koneksi statement
    $stmt->close();
    $Conn->close();
    
    //Jika Tidak ada pengaturan
    if (empty($DataParam['id_setting_payment'])) {
        $id_setting_payment = 0;
        $status             = "No Setting";
        $datetime           = date('Y-m-d H:i:s');
        $order_id           = "";
        $insert_log         = insertLog($Conn, $id_setting_payment, $order_id, $datetime, $status);
        echo "No Setting";
        exit;
    }

    //Jika Ada Pengaturan
    $id_setting_payment = $DataParam['id_setting_payment'];

    //Library Midtrans
    require_once "../midtrans-php-master/Midtrans.php";

    // Konfigurasi Koneksi Midtrans
    \Midtrans\Config::$isProduction = $DataParam['production'] === "true";
    \Midtrans\Config::$serverKey = $DataParam['server_key'];

    $notif = new \Midtrans\Notification();

    // Membuat Variabel Lainnya dari Notifikasi Midtrans
    $transaction_time   = $notif->transaction_time;
    $status_code        = $notif->status_code;
    $transaction_status = $notif->transaction_status;
    $order_id           = $notif->order_id;
    $payment_type       = $notif->payment_type;
    $gross_amount       = $notif->gross_amount;
    $fraud_status       = $notif->fraud_status;

    // Mengambil kode_transaksi dari order_id
    $kode_transaksi = getDataDetail($Conn, 'transaction ', 'order_id', $order_id, 'kode_transaksi');
    $id_transaction = getDataDetail($Conn, 'transaction ', 'order_id', $order_id, 'id_transaction');
    
    // Simpan Log Notifikasi
    $stmt = $Conn->prepare("
        INSERT INTO log_payment (
            id_transaction, 
            kode_transaksi, 
            order_id, 
            transaction_time, 
            status_code, 
            payment_type, 
            gross_amount, 
            fraud_status, 
            transaction_status
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'sssssssss', 
        $id_transaction, 
        $kode_transaksi, 
        $order_id, 
        $transaction_time, 
        $status_code, 
        $payment_type, 
        $gross_amount, 
        $fraud_status, 
        $transaction_status
    );
    
    $Input = $stmt->execute();
    if ($Input) {
        
        //Apabila Ada URL Call Back
        if (!empty($DataParam['urll_call_back'])) {
            if($transaction_status=="settlement"){
                $status_transaksi="Lunas";
            }else{
                $status_transaksi="Pending";
            }
            //Kirim Data Melalui API
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => ''.$DataParam['urll_call_back'].'',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "api_key": "'.$DataParam['api_key'].'",
                "order_id": "'.$order_id.'",
                "status": "'.$status_transaksi.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            echo "$response";
            $id_setting_payment = $DataParam['id_setting_payment'];
            $status             = "$response";
            $datetime           = date('Y-m-d H:i:s');
            $insert_log         = insertLog($Conn, $id_setting_payment, $order_id, $datetime, $status);
        }else{
            echo "URL Call Back No Set";
            $id_setting_payment = $DataParam['id_setting_payment'];
            $status             = "URL Call Back No Set";
            $datetime           = date('Y-m-d H:i:s');
            $insert_log         = insertLog($Conn, $id_setting_payment, $order_id, $datetime, $status);
        }
    } else {
        echo "Input Data log_payment Gagal: " . $stmt->error;
        $id_setting_payment = $DataParam['id_setting_payment'];
        $status             = "Input Data log_payment Gagal: " . $stmt->error;
        $datetime           = date('Y-m-d H:i:s');
        $insert_log         = insertLog($Conn, $id_setting_payment, $order_id, $datetime, $status);
    }
?>
