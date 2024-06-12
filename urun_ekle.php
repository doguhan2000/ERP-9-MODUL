<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urun_adi = $_POST['urun_adi'];
    $urun_aciklamasi = $_POST['urun_aciklamasi'];
    $fiyat = $_POST['fiyat'];
    $stok = $_POST['stok'];
    $urun_fotografi = $_FILES['urun_fotografi']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($urun_fotografi);

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['urun_fotografi']['tmp_name'], $target_file)) {

        $sql = "INSERT INTO urunler (urun_adi, urun_aciklamasi, fiyat, stok, urun_fotografi) VALUES ('$urun_adi', '$urun_aciklamasi', '$fiyat', '$stok', '$target_file')";

        if ($conn->query($sql) === TRUE) {
            header("Location: urunler.php");
            exit();
        } else {
            echo "Hata: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Dosya yükleme hatası.";
    }
}

$conn->close();
?>
