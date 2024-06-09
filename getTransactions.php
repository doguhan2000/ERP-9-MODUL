<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT id, tarih, tur, miktar, aciklama, vergi_orani, para_birimi FROM finance_management";
$result = $conn->query($sql);

$transactions = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

echo json_encode($transactions);

$conn->close();
?>
