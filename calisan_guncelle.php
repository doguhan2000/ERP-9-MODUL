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
    $sql = "SELECT ad_soyad, pozisyon, departman, maas, ise_giris_tarihi FROM insan_kaynaklari WHERE id='$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Çalışan Güncelle</title>
            <link rel="stylesheet" href="hr.css">
        </head>
        <body>
            <div id="container">
                <aside id="sidebar">
                    <button id="menu-toggle"><i class="fas fa-bars"></i></button>
                    <h2>Menü</h2>
                    <ul>
                        <li><a href="calisanlar.php">Çalışanlar</a></li>
                        <li><a href="departmanlar.php">Departmanlar</a></li>
                        <li><a href="raporlar.php">Raporlar</a></li>
                    </ul>
                </aside>
                <main id="main-content">
                    <button onclick="history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Geri</button>
                    <section id="employees" class="content-section active">
                        <h2>Çalışan Güncelle</h2>
                        <form id="employee-form" action="calisan_guncelle_islem.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="form-group">
                                <label for="name">İsim:</label>
                                <input type="text" id="name" name="name" value="<?php echo $row['ad_soyad']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="position">Pozisyon:</label>
                                <input type="text" id="position" name="position" value="<?php echo $row['pozisyon']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="department">Departman:</label>
                                <input type="text" id="department" name="department" value="<?php echo $row['departman']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="hire_date">İşe Giriş Tarihi:</label>
                                <input type="date" id="hire_date" name="hire_date" value="<?php echo $row['ise_giris_tarihi']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="salary">Maaş:</label>
                                <input type="number" id="salary" name="salary" step="0.01" value="<?php echo $row['maas']; ?>" required>
                            </div>
                            <button type="submit" class="btn"><i class="fas fa-save"></i> Güncelle</button>
                        </form>
                    </section>
                </main>
            </div>
            <script src="hr.js"></script>
        </body>
        </html>
        <?php
    } else {
        echo "Çalışan bulunamadı";
    }
}

$conn->close();
?>
