<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performans Ekle</title>
    <link rel="stylesheet" href="hr.css">
</head>
<body>
    <form action="performans_ekle.php" method="post">
        <div class="form-group">
            <label for="calisan_id">Çalışan ID:</label>
            <input type="text" id="calisan_id" name="calisan_id" required>
        </div>
        <div class="form-group">
            <label for="tarih">Tarih:</label>
            <input type="date" id="tarih" name="tarih" required>
        </div>
        <div class="form-group">
            <label for="performans_puani">Performans Puanı:</label>
            <input type="number" id="performans_puani" name="performans_puani" required>
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</body>
</html>
