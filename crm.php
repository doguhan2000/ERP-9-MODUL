<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri İlişkileri Yönetimi</title>
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
            height: 100vh;
            background-image: url('crm.jpg'); 
            background-size: cover;
            background-position: center;
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
        <h1>Müşteri İlişkileri Yönetimi</h1>
        <p>Takımınızın her boyutu için müşteri destek yazılımı</p>
        <div class="nav">
            <button onclick="window.location.href='ticket_list.php'">Ticket Yönetimi</button>
            <button onclick="window.location.href='customer_list.php'">Müşteri Listesi</button>
            <button onclick="window.location.href='customer_add.php'">Müşteri Ekle</button>
            <button onclick="window.location.href='report.php'">Raporlar</button>
            <button onclick="window.location.href='index.html'">Ana Sayfaya Dön</button>
        </div>
    </div>
</body>
</html>
