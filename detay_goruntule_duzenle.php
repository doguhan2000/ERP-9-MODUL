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
    $tedarikci_id = $_POST['tedarikci_id'];
    $urun_adi = $_POST['urun_adi'];
    $miktar = $_POST['miktar'];
    $birim_fiyat = $_POST['birim_fiyat'];
    $toplam_fiyat = $miktar * $birim_fiyat;
    $siparis_tarihi = $_POST['siparis_tarihi'];
    $durum = $_POST['durum'];

    $sql = "UPDATE satin_alma_siparisleri 
            SET tedarikci_id='$tedarikci_id', urun_adi='$urun_adi', miktar='$miktar', birim_fiyat='$birim_fiyat', toplam_fiyat='$toplam_fiyat', siparis_tarihi='$siparis_tarihi', durum='$durum'
            WHERE id='$siparis_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: siparisler.php");
        exit();
    } else {
        echo "Hata: " . $sql . "<br>" . $conn->error;
    }
} else {
    $siparis_id = $_GET['id'];
    $sql = "SELECT * FROM satin_alma_siparisleri WHERE id='$siparis_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $tedarikci_id = $row['tedarikci_id'];
        $urun_adi = $row['urun_adi'];
        $miktar = $row['miktar'];
        $birim_fiyat = $row['birim_fiyat'];
        $siparis_tarihi = $row['siparis_tarihi'];
        $durum = $row['durum'];
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
    <title>Sipariş Detayları</title>
    <link rel="stylesheet" href="siparisler.css">
</head>
<body>
    <div id="container">
        <h1>Sipariş Detayları</h1>
        <form action="detay_goruntule_duzenle.php" method="post">
            <input type="hidden" name="siparis_id" value="<?php echo $siparis_id; ?>">
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
                        while($tedarikci = $result->fetch_assoc()) {
                            $selected = ($tedarikci['id'] == $tedarikci_id) ? 'selected' : '';
                            echo "<option value='" . $tedarikci['id'] . "' $selected>" . $tedarikci['tedarikci_adi'] . "</option>";
                        }
                    }
                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="urun_adi">Ürün Adı:</label>
                <input type="text" id="urun_adi" name="urun_adi" value="<?php echo $urun_adi; ?>" required>
            </div>
            <div class="form-group">
                <label for="miktar">Miktar:</label>
                <input type="number" id="miktar" name="miktar" value="<?php echo $miktar; ?>" required>
            </div>
            <div class="form-group">
                <label for="birim_fiyat">Birim Fiyat:</label>
                <input type="number" step="0.01" id="birim_fiyat" name="birim_fiyat" value="<?php echo $birim_fiyat; ?>" required>
            </div>
            <div class="form-group">
                <label for="siparis_tarihi">Sipariş Tarihi:</label>
                <input type="date" id="siparis_tarihi" name="siparis_tarihi" value="<?php echo $siparis_tarihi; ?>" required>
            </div>
            <div class="form-group">
                <label for="durum">Durum:</label>
                <select id="durum" name="durum" required>
                    <option value="Beklemede" <?php if($durum == 'Beklemede') echo 'selected'; ?>>Beklemede</option>
                    <option value="Tamamlandı" <?php if($durum == 'Tamamlandı') echo 'selected'; ?>>Tamamlandı</option>
                    <option value="İptal Edildi" <?php if($durum == 'İptal Edildi') echo 'selected'; ?>>İptal Edildi</option>
                </select>
            </div>
            <button type="submit" class="form-btn">Güncelle</button>
        </form>
        <button onclick="window.location.href='siparisler.php'" class="form-btn">Siparişlere Dön</button>
    </div>
</body>
</html>
