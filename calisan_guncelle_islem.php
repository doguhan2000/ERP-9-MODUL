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
    $id = $_POST['id'];
    $ad_soyad = $_POST['name'];
    $pozisyon = $_POST['position'];
    $departman = $_POST['department'];
    $maas = $_POST['salary'];
    $ise_giris_tarihi = $_POST['hire_date'];

    $sql = "UPDATE insan_kaynaklari SET ad_soyad='$ad_soyad', pozisyon='$pozisyon', departman='$departman', maas='$maas', ise_giris_tarihi='$ise_giris_tarihi' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "Çalışan başarıyla güncellendi";
    } else {
        echo "Hata: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
