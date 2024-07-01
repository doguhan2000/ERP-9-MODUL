<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Ticket Oluştur</title>
    <link rel="stylesheet" href="crm.css">
</head>
<body>
    <div id="container">
        <h1>Yeni Ticket Oluştur</h1>
        <form action="ticket_create_process.php" method="post">
            <label for="title">Başlık:</label>
            <input type="text" id="title" name="title" required>

            <label for="customer_id">Müşteri:</label>
            <select id="customer_id" name="customer_id" required>
                <?php
                include 'db_connect.php';
                $sql = "SELECT id, ad_soyad FROM musteriler";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['ad_soyad']}</option>";
                    }
                } else {
                    echo "<option value=''>Müşteri bulunamadı</option>";
                }
                ?>
            </select>

            <label for="employee_id">İlgilenecek Çalışan:</label>
            <select id="employee_id" name="employee_id" required>
                <?php
                $sql = "SELECT id, ad_soyad FROM insan_kaynaklari";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['ad_soyad']}</option>";
                    }
                } else {
                    echo "<option value=''>Çalışan bulunamadı</option>";
                }
                $conn->close();
                ?>
            </select>

            <label for="description">Açıklama:</label>
            <textarea id="description" name="description" required></textarea>
            <button type="submit">Oluştur</button>
        </form>
        <button onclick="window.location.href='ticket_list.php'">Geri Dön</button>
    </div>
</body>
</html>
