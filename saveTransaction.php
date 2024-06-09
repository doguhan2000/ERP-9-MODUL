<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$tarih = $_POST['date'];
$tur = $_POST['type'];
$miktar = $_POST['amount'];
$aciklama = $_POST['description'];
$vergi_orani = $_POST['tax'];
$para_birimi = $_POST['currency'];


$sql = "INSERT INTO finance_management (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdsss", $tarih, $tur, $miktar, $aciklama, $vergi_orani, $para_birimi);

$response = array();

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['error'] = $stmt->error;
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>
