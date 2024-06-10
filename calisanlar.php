<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çalışanlar</title>
    <link rel="stylesheet" href="hr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <h2>Çalışanlar</h2>
                <form id="employee-form" action="calisan_ekle.php" method="post">
                    <div class="form-group">
                        <label for="name">İsim:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="position">Pozisyon:</label>
                        <input type="text" id="position" name="position" required>
                    </div>
                    <div class="form-group">
                        <label for="hire_date">İşe Giriş Tarihi:</label>
                        <input type="date" id="hire_date" name="hire_date" required>
                    </div>
                    <div class="form-group">
                        <label for="salary">Maaş:</label>
                        <input type="number" id="salary" name="salary" step="0.01" required>
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Kaydet</button>
                </form>

                <h2>Çalışan Listesi</h2>
                <div id="employee-list">
                    <table>
                        <thead>
                            <tr>
                                <th>İsim</th>
                                <th>Pozisyon</th>
                                <th>Departman</th>
                                <th>Maaş</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Veritabanı bağlantısı
                            $servername = "localhost";
                            $username = "root";
                            $password = "";
                            $dbname = "erp_db";

                            $conn = new mysqli($servername, $username, $password, $dbname);

                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            // Çalışanları veritabanından çek
                            $sql = "SELECT ad_soyad, pozisyon, maas, ise_giris_tarihi FROM insan_kaynaklari";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . $row["ad_soyad"] . "</td>
                                            <td>" . $row["pozisyon"] . "</td>
                                            <td>" . $row["maas"] . "</td>
                                            <td>" . $row["ise_giris_tarihi"] . "</td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Hiç çalışan yok</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script src="hr.js"></script>
</body>
</html>
