<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satın Alma Siparişleri</title>
    <link rel="stylesheet" href="siparisler.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="container">
        <button onclick="window.location.href='index.html'" class="home-btn">Anasayfaya Dön</button>
        <h1>Satın Alma Siparişleri</h1>
        <button onclick="window.location.href='tedarikci_ekle.php'" class="add-btn">Tedarikçi Ekle</button>
        <button onclick="window.location.href='siparis_ekle.php'" class="add-btn">Sipariş Ekle</button>
        <table>
            <thead>
                <tr>
                    <th>Sipariş ID</th>
                    <th>Tedarikçi Adı</th>
                    <th>Ürün Adı</th>
                    <th>Miktar</th>
                    <th>Birim Fiyat</th>
                    <th>Toplam Fiyat</th>
                    <th>Sipariş Tarihi</th>
                    <th>Durum</th>
                    <th>Durum Güncelle</th>
                    <th>Detay Görüntüle/Düzenle</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conn = new mysqli("localhost", "root", "", "erp_db");

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT sa.id, t.tedarikci_adi, sa.urun_adi, sa.miktar, sa.birim_fiyat, sa.toplam_fiyat, sa.siparis_tarihi, sa.durum
                        FROM satin_alma_siparisleri sa
                        JOIN tedarikciler t ON sa.tedarikci_id = t.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['tedarikci_adi'] . "</td>";
                        echo "<td>" . $row['urun_adi'] . "</td>";
                        echo "<td>" . $row['miktar'] . "</td>";
                        echo "<td>" . $row['birim_fiyat'] . "</td>";
                        echo "<td>" . $row['toplam_fiyat'] . "</td>";
                        echo "<td>" . $row['siparis_tarihi'] . "</td>";
                        echo "<td>" . $row['durum'] . "</td>";
                        echo "<td><a href='guncelle_durum.php?id=" . $row['id'] . "'><i class='fas fa-edit'></i></a></td>";
                        echo "<td><a href='detay_goruntule_duzenle.php?id=" . $row['id'] . "'><i class='fas fa-eye'></i></a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>Henüz sipariş eklenmemiş.</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
