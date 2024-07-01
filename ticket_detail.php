<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Detayları</title>
    <link rel="stylesheet" href="crm.css">
</head>
<body>
    <div id="container">
        <h1>Ticket Detayları</h1>
        <?php
        include 'db_connect.php';
        $id = $_GET['id'];
        $sql = "SELECT t.id, t.title, t.description, t.status, t.created_at, i.ad_soyad AS customer 
                FROM tickets t 
                JOIN insan_kaynaklari i ON t.customer_id = i.id 
                WHERE t.id='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            echo "<h2>{$row['title']}</h2>";
            echo "<p>Müşteri: {$row['customer']}</p>";
            echo "<p>Açıklama: {$row['description']}</p>";
            echo "<p>Durum: {$row['status']}</p>";
            echo "<p>Oluşturulma Tarihi: {$row['created_at']}</p>";
        } else {
            echo "<p>Ticket bulunamadı.</p>";
        }
        $conn->close();
        ?>
        <button onclick="window.location.href='ticket_list.php'">Geri Dön</button>
    </div>
</body>
</html>
