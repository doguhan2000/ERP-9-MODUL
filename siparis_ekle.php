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
    $tedarikci_id = $_POST['tedarikci_id'];
    $urun_adi = $_POST['urun_adi'];
    $miktar = $_POST['miktar'];
    $birim_fiyat = $_POST['birim_fiyat'];
    $toplam_fiyat = $miktar * $birim_fiyat;
    $siparis_tarihi = date('Y-m-d');

    $sql = "INSERT INTO satin_alma_siparisleri (tedarikci_id, urun_adi, miktar, birim_fiyat, toplam_fiyat, siparis_tarihi, durum)
            VALUES ('$tedarikci_id', '$urun_adi', '$miktar', '$birim_fiyat', '$toplam_fiyat', '$siparis_tarihi', 'Beklemede')";

    if ($conn->query($sql) === TRUE) {
        header("Location: siparisler.php");
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
    <title>Satın Alma Siparişi Ekle</title>
    <link rel="stylesheet" href="siparisler.css">
</head>
<body>
    <div id="container">
        <h1>Satın Alma Siparişi Ekle</h1>
        <form action="siparis_ekle.php" method="post">
            <div class="form-group">
                <label for="tedarikci_id">Tedarikçi:</label>
                <select id="tedarikci_id" name="tedarikci_id" required>
                    <?php
                    $conn = new mysqli("localhost", "root", "", "erp_db");
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    $sql = "SELECT id, tedarikci_adi FROM tedarikciler";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['tedarikci_adi'] . "</option>";
                        }
                    }
                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="urun_adi">Ürün Adı:</label>
                <input type="text" id="urun_adi" name="urun_adi" required>
            </div>
            <div class="form-group">
                <label for="miktar">Miktar:</label>
                <input type="number" id="miktar" name="miktar" required>
            </div>
            <div class="form-group">
                <label for="birim_fiyat">Birim Fiyat:</label>
                <input type="number" step="0.01" id="birim_fiyat" name="birim_fiyat" required>
            </div>
            <button type="submit" class="form-btn">Siparişi Ekle</button>
        </form>
        <button onclick="window.location.href='siparisler.php'" class="form-btn">Siparişlere Dön</button>
    </div>
</body>
</html>
