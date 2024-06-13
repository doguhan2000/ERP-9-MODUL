<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

// Veritabanı bağlantısını oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tedarikci_adi = $_POST['tedarikci_adi'];
    $tedarikci_iletisim = $_POST['tedarikci_iletisim'];
    $tedarikci_adresi = $_POST['tedarikci_adresi'];
    $tedarikci_telefonu = $_POST['tedarikci_telefonu'];
    $tedarikci_eposta = $_POST['tedarikci_eposta'];

    $sql = "INSERT INTO tedarikciler (tedarikci_adi, tedarikci_iletisim, tedarikci_adresi, tedarikci_telefonu, tedarikci_eposta)
            VALUES ('$tedarikci_adi', '$tedarikci_iletisim', '$tedarikci_adresi', '$tedarikci_telefonu', '$tedarikci_eposta')";

    if ($conn->query($sql) === TRUE) {
        header("Location: siparis_ekle.php");
        exit();
    } else {
        echo "Hata: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tedarikçi Ekle</title>
    <link rel="stylesheet" href="siparisler.css">
</head>
<body>
    <div id="container">
        <h1>Tedarikçi Ekle</h1>
        <form action="tedarikci_ekle.php" method="post">
            <div class="form-group">
                <label for="tedarikci_adi">Tedarikçi Adı:</label>
                <input type="text" id="tedarikci_adi" name="tedarikci_adi" required>
            </div>
            <div class="form-group">
                <label for="tedarikci_iletisim">İletişim Bilgileri:</label>
                <input type="text" id="tedarikci_iletisim" name="tedarikci_iletisim" required>
            </div>
            <div class="form-group">
                <label for="tedarikci_adresi">Adres:</label>
                <textarea id="tedarikci_adresi" name="tedarikci_adresi" required></textarea>
            </div>
            <div class="form-group">
                <label for="tedarikci_telefonu">Telefon:</label>
                <input type="text" id="tedarikci_telefonu" name="tedarikci_telefonu" required>
            </div>
            <div class="form-group">
                <label for="tedarikci_eposta">E-posta:</label>
                <input type="email" id="tedarikci_eposta" name="tedarikci_eposta" required>
            </div>
            <button type="submit" class="form-btn">Tedarikçiyi Ekle</button>
        </form>
        <button onclick="window.location.href='siparisler.php'" class="form-btn">Siparişlere Dön</button>
    </div>
</body>
</html>
