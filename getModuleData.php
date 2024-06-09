<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "erp_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$module_id = $_GET['module'];

$sql = "SELECT id, name, description FROM modules WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$result = $stmt->get_result();

$module = $result->fetch_assoc();

echo json_encode($module);

$stmt->close();
$conn->close();
?>
