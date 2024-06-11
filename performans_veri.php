<?php
// performans_veri.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT ik.ad_soyad AS name, p.performans_puani AS score
        FROM performans p
        JOIN insan_kaynaklari ik ON p.calisan_id = ik.id";
$result = $conn->query($sql);

$performanceData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $performanceData[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($performanceData);
?>
