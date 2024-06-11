<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devamlılık Ekle</title>
    <link rel="stylesheet" href="hr.css">
</head>
<body>
    <form action="devamlilik_ekle.php" method="post">
        <div class="form-group">
            <label for="calisan_id">Çalışan ID:</label>
            <input type="text" id="calisan_id" name="calisan_id" required>
        </div>
        <div class="form-group">
            <label for="tarih">Tarih:</label>
            <input type="date" id="tarih" name="tarih" required>
        </div>
        <div class="form-group">
            <label for="durum">Durum:</label>
            <select id="durum" name="durum" required>
                <option value="izin">İzin</option>
                <option value="devamsizlik">Devamsızlık</option>
                <option value="calisma">Çalışma</option>
            </select>
        </div>
        <button type="submit" class="btn">Kaydet</button>
    </form>
</body>
</html>
