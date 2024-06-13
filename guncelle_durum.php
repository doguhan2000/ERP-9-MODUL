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
    $siparis_id = $_POST['siparis_id'];
    $yeni_durum = $_POST['yeni_durum'];

    $sql = "UPDATE satin_alma_siparisleri SET durum='$yeni_durum' WHERE id='$siparis_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: siparisler.php");
        exit();
    } else {
        echo "Hata: " . $sql . "<br>" . $conn->error;
    }
} else {
    $siparis_id = $_GET['id'];
    $sql = "SELECT id, durum FROM satin_alma_siparisleri WHERE id='$siparis_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mevcut_durum = $row['durum'];
    } else {
        echo "Hata: Sipariş bulunamadı.";
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Durum Güncelle</title>
    <link rel="stylesheet" href="siparisler.css">
</head>
<body>
    <div id="container">
        <h1>Durum Güncelle</h1>
        <form action="guncelle_durum.php" method="post">
            <input type="hidden" name="siparis_id" value="<?php echo $siparis_id; ?>">
            <div class="form-group">
                <label for="yeni_durum">Yeni Durum:</label>
                <select id="yeni_durum" name="yeni_durum" required>
                    <option value="Beklemede" <?php if($mevcut_durum == 'Beklemede') echo 'selected'; ?>>Beklemede</option>
                    <option value="Tamamlandı" <?php if($mevcut_durum == 'Tamamlandı') echo 'selected'; ?>>Tamamlandı</option>
                    <option value="İptal Edildi" <?php if($mevcut_durum == 'İptal Edildi') echo 'selected'; ?>>İptal Edildi</option>
                </select>
            </div>
            <button type="submit" class="form-btn">Durumu Güncelle</button>
        </form>
        <button onclick="window.location.href='siparisler.php'" class="form-btn">Siparişlere Dön</button>
    </div>
</body>
</html>
