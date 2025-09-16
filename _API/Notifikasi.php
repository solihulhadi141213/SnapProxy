<?php
    include "_Config/Connection.php";
    include "_Config/Function.php";
    
    // Buka Pengaturan / Setting
    $QryParam = $Conn->prepare("SELECT * FROM setting_payment WHERE id_setting_payment = ?");
    $id_setting_payment = 1;
    $QryParam->bind_param('i', $id_setting_payment);
    $QryParam->execute();
    $ResultParam = $QryParam->get_result();
    $DataParam = $ResultParam->fetch_array();
    
    if (empty($DataParam['server_key'])) {
        $server_key = "";
        $notifikasi = [
            "status" => 'server_key null',
            "payment_log" => ''
        ];
        $kode_transaksi = "";
        $order_id = "";
        $transaction_time = date('Y-m-d H:i:s');
        $status_code = "0";
        $transaction_status = "";
        $payment_type = "";
        $gross_amount = "0";
        $fraud_status = "";
    } else {
        if (empty($DataParam['production'])) {
            $notifikasi = [
                "status" => 'production null',
                "payment_log" => ''
            ];
            $kode_transaksi = "";
            $order_id = "";
            $transaction_time = date('Y-m-d H:i:s');
            $status_code = "0";
            $transaction_status = "";
            $payment_type = "";
            $gross_amount = "0";
            $fraud_status = "";
        } else {
            require_once "midtrans-php-master/Midtrans.php";
    
            // Konfigurasi Koneksi Midtrans
            \Midtrans\Config::$isProduction = $DataParam['production'] === "true";
            \Midtrans\Config::$serverKey = $DataParam['server_key'];
    
            $notif = new \Midtrans\Notification();
    
            // Membuat Variabel Lainnya dari Notifikasi Midtrans
            $transaction_time = $notif->transaction_time;
            $status_code = $notif->status_code;
            $transaction_status = $notif->transaction_status;
            $order_id = $notif->order_id;
            $payment_type = $notif->payment_type;
            $gross_amount = $notif->gross_amount;
            $fraud_status = $notif->fraud_status;
    
            // Mengambil kode_transaksi dari order_id
            $kode_transaksi = getDataDetail($Conn, 'order_transaksi', 'order_id', $order_id, 'kode_transaksi');
        }
    }
    
    // Buat Json Notifikasi
    $notifikasi['payment_log'] = [
        'kode_transaksi' => $kode_transaksi,
        'order_id' => $order_id,
        'transaction_time' => $transaction_time,
        'status_code' => $status_code,
        'transaction_status' => $transaction_status,
        'payment_type' => $payment_type,
        'gross_amount' => $gross_amount,
        'fraud_status' => $fraud_status
    ];
    
    $jsonNotif = json_encode($notifikasi);
    
    // Simpan Log Notifikasi
    $stmt = $Conn->prepare("
        INSERT INTO log_payment 
        (kode_transaksi, order_id, transaction_time, status_code, payment_type, gross_amount, fraud_status, transaction_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'ssssssss', 
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
        }
        echo "Success";
    } else {
        echo "Input Data log_payment Gagal: " . $stmt->error;
    }
    
    // Menutup koneksi statement
    $stmt->close();
    $Conn->close();
?>
