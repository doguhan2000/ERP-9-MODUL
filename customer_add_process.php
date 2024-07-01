<?php
include 'db_connect.php';

$ad_soyad = $_POST['ad_soyad'];
$email = $_POST['email'];
$telefon = $_POST['telefon'];
$adres = $_POST['adres'];

$sql = "INSERT INTO musteriler (ad_soyad, email, telefon, adres) VALUES ('$ad_soyad', '$email', '$telefon', '$adres')";

if ($conn->query($sql) === TRUE) {
    header("Location: customer_list.php"); // Başarılı ekleme sonrası yönlendirme
} else {
    echo "Hata: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
