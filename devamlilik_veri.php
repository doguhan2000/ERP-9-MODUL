<?php
// devamlilik_veri.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT ik.ad_soyad AS name, d.tarih AS date, d.durum AS status
        FROM devamlilik d
        JOIN insan_kaynaklari ik ON d.calisan_id = ik.id";
$result = $conn->query($sql);

$attendanceData = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $attendanceData[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($attendanceData);
?>
