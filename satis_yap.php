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
    $urun_id = $_POST['urun_id'];
    $miktar = $_POST['miktar'];

    $sql = "SELECT stok, fiyat FROM urunler WHERE id=$urun_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stok = $row['stok'];
    $fiyat = $row['fiyat'];

    if ($stok >= $miktar) {
        $yeni_stok = $stok - $miktar;
        $toplam_fiyat = $fiyat * $miktar;

        $sql = "UPDATE urunler SET stok=$yeni_stok WHERE id=$urun_id";
        if ($conn->query($sql) === TRUE) {

            $sql = "INSERT INTO satislar (urun_id, miktar, toplam_fiyat, satis_tarihi) VALUES ('$urun_id', '$miktar', '$toplam_fiyat', CURDATE())";
            if ($conn->query($sql) === TRUE) {
                header("Location: urunler.php");
                exit();
            } else {
                echo "Satış kaydı eklenirken hata oluştu: " . $conn->error;
            }
        } else {
            echo "Stok güncellenirken hata oluştu: " . $conn->error;
        }
    } else {
        echo "Yeterli stok yok.";
    }
} else {
    $urun_id = $_GET['urun_id'];
    $sql = "SELECT urun_adi, fiyat FROM urunler WHERE id=$urun_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $urun_adi = $row['urun_adi'];
    $fiyat = $row['fiyat'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Yap</title>
    <link rel="stylesheet" href="satis_yap.css">
</head>
<body>
    <div id="container">
        <h1>Satış Yap</h1>
        <form action="satis_yap.php" method="post">
            <div class="form-group">
                <label for="urun_adi">Ürün Adı:</label>
                <input type="text" id="urun_adi" name="urun_adi" value="<?php echo $urun_adi; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="fiyat">Fiyat:</label>
                <input type="text" id="fiyat" name="fiyat" value="<?php echo $fiyat; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="miktar">Miktar:</label>
                <input type="number" id="miktar" name="miktar" required>
            </div>
            <input type="hidden" name="urun_id" value="<?php echo $urun_id; ?>">
            <button type="submit">Satış Yap</button>
        </form>
    </div>
</body>
</html>
<?php
}

$conn->close();
?>
