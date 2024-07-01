<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Müşteri Ekle</title>
    <link rel="stylesheet" href="crm.css">
</head>
<body>
    <div id="container">
        <h1>Yeni Müşteri Ekle</h1>
        <form action="customer_add_process.php" method="post">
            <label for="ad_soyad">Ad Soyad:</label>
            <input type="text" id="ad_soyad" name="ad_soyad" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="telefon">Telefon:</label>
            <input type="text" id="telefon" name="telefon" required>

            <label for="adres">Adres:</label>
            <textarea id="adres" name="adres" required></textarea>

            <button type="submit">Ekle</button>
        </form>
        <button onclick="window.location.href='crm.php'">Geri Dön</button>
    </div>
</body>
</html>
