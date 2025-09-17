<?php
header('Content-Type: application/json');

// Pastikan hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['string_asli']) && !empty($_POST['string_asli'])) {
        $string_asli = $_POST['string_asli'];

        // Generate hash PASSWORD_DEFAULT
        $hash = password_hash($string_asli, PASSWORD_DEFAULT);

        echo json_encode([
            "status" => "success",
            "hash" => $hash
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "String kosong"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Metode request tidak valid"
    ]);
}

?>