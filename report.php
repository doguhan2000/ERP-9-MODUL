<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar</title>
    <link rel="stylesheet" href="crm.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        #container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h1 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .nav button {
            margin: 10px;
            padding: 15px 30px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .nav button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div id="container">
        <h1>Raporlar</h1>
        <div class="nav">
            <button onclick="window.location.href='crm.php'">Geri Dön</button>
        </div>
        <h2>Müşteri Raporu</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad Soyad</th>
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

        <h2>Ticket Durum Raporu</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Başlık</th>
                    <th>Müşteri</th>
                    <th>Çalışan</th>
                    <th>Durum</th>
                    <th>Oluşturulma Tarihi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db_connect.php';
                $sql = "SELECT t.id, t.title, m.ad_soyad AS customer, i.ad_soyad AS employee, t.status, t.created_at 
                        FROM tickets t 
                        JOIN musteriler m ON t.customer_id = m.id 
                        JOIN insan_kaynaklari i ON t.employee_id = i.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['customer']}</td>
                                <td>{$row['employee']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['created_at']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Kayıt bulunamadı.</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
