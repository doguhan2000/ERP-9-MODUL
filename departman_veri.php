<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

// Veritabanı bağlantısını oluştur
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Departman verilerini çek
$sql_departments = "SELECT departman, COUNT(*) as count FROM insan_kaynaklari GROUP BY departman";
$result_departments = $conn->query($sql_departments);

$departments = [];

if ($result_departments->num_rows > 0) {
    while($row = $result_departments->fetch_assoc()) {
        $departments[$row['departman']] = $row['count'];
    }
}

// Maaş aralıklarını çek
$sql_salaries = "
    SELECT 
        CASE 
            WHEN maas < 3000 THEN '0-2999'
            WHEN maas BETWEEN 3000 AND 4999 THEN '3000-4999'
            WHEN maas BETWEEN 5000 AND 6999 THEN '5000-6999'
            WHEN maas BETWEEN 7000 AND 9999 THEN '7000-9999'
            ELSE '10000+' 
        END as salary_range,
        COUNT(*) as count
    FROM insan_kaynaklari
    GROUP BY salary_range
";
$result_salaries = $conn->query($sql_salaries);

$salaryRanges = [];

if ($result_salaries->num_rows > 0) {
    while($row = $result_salaries->fetch_assoc()) {
        $salaryRanges[] = [
            'range' => $row['salary_range'],
            'count' => $row['count']
        ];
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['departments' => $departments, 'salaryRanges' => $salaryRanges]);
?>
