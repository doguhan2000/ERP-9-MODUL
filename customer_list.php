<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Listesi</title>
    <link rel="stylesheet" href="crm.css">
</head>
<body>
    <div id="container">
        <h1>Müşteri Listesi</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>İsim</th>
                    <th>Email</th>
                    <th>Telefon</th>
                    <th>Adres</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db_connect.php';
                
                $sql = "SELECT * FROM musteriler";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['ad_soyad']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['telefon']}</td>
                                <td>{$row['adres']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Kayıt bulunamadı.</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
        <button onclick="window.location.href='crm.php'">Geri Dön</button>
    </div>
</body>
</html>
