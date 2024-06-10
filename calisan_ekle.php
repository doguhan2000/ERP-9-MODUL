<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

// Veritabanı bağlantısını oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad_soyad = $_POST['name'];
    $pozisyon = $_POST['position'];
    $maas = $_POST['salary'];
    $ise_giris_tarihi = $_POST['hire_date'];

    $sql = "INSERT INTO insan_kaynaklari (ad_soyad, pozisyon, maas, ise_giris_tarihi) VALUES ('$ad_soyad', '$pozisyon', '$maas', '$ise_giris_tarihi')";

    if ($conn->query($sql) === TRUE) {
        echo "Yeni çalışan başarıyla eklendi";
    } else {
        echo "Hata: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
