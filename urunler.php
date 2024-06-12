<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünler</title>
    <link rel="stylesheet" href="satis.css">
</head>
<body>
    <div id="container">
        <button onclick="window.location.href='index.html'" class="back-btn"><i class="fas fa-arrow-left"></i> Anasayfaya Dön</button>
        <button onclick="window.location.href='urun_ekle.html'" class="add-btn">Ürün Ekle</button>
        <h1>Ürünler</h1>
        <div class="product-grid">
            <?php
            $conn = new mysqli("localhost", "root", "", "erp_db");

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT * FROM urunler";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='product-item'>";
                    echo "<img src='" . $row['urun_fotografi'] . "' alt='" . $row['urun_adi'] . "'>";
                    echo "<h2>" . $row['urun_adi'] . "</h2>";
                    echo "<p>" . $row['urun_aciklamasi'] . "</p>";
                    echo "<p>Fiyat: " . $row['fiyat'] . " TL</p>";
                    echo "<p>Stok: " . $row['stok'] . "</p>";
                    echo "<button onclick='satisYap(" . $row['id'] . ")'>Satış Yap</button>";
                    echo "</div>";
                }
            } else {
                echo "Henüz ürün eklenmemiş.";
            }

            $conn->close();
            ?>
        </div>
    </div>
    <script>
        function satisYap(urunId) {
            window.location.href = 'satis_yap.php?urun_id=' + urunId;
        }
    </script>
</body>
</html>
