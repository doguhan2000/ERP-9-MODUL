<?php
include 'db_connect.php';

$title = $_POST['title'];
$customer_id = $_POST['customer_id'];
$employee_id = $_POST['employee_id'];
$description = $_POST['description'];
$status = 'Açık'; 

$sql = "INSERT INTO tickets (title, customer_id, employee_id, description, status) 
        VALUES ('$title', '$customer_id', '$employee_id', '$description', '$status')";

if ($conn->query($sql) === TRUE) {
    header("Location: ticket_list.php"); 
} else {
    echo "Hata: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
