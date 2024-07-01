<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Yönetimi</title>
    <link rel="stylesheet" href="crm.css">
</head>
<body>
    <div id="container">
        <h1>Ticket Yönetimi</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Başlık</th>
                    <th>Müşteri</th>
                    <th>Durum</th>
                    <th>Oluşturulma Tarihi</th>
                    <th>Aksiyonlar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db_connect.php';
                $sql = "SELECT t.id, t.title, i.ad_soyad AS customer, t.status, t.created_at FROM tickets t 
                        JOIN insan_kaynaklari i ON t.customer_id = i.id";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['title']}</td>
                                <td>{$row['customer']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['created_at']}</td>
                                <td><a href='ticket_detail.php?id={$row['id']}'>Detaylar</a></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Henüz ticket yok.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
        <button onclick="window.location.href='ticket_create.php'">Yeni Ticket Oluştur</button>
        <button onclick="window.location.href='crm.php'">Geri Dön</button>
    </div>
</body>
</html>
