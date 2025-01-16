<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['customer_id']) || !isset($_POST['title']) || !isset($_POST['type']) || 
        !isset($_POST['start_date']) || !isset($_POST['due_date'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        INSERT INTO tasks (customer_id, title, description, type, start_date, due_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['customer_id'],
        $_POST['title'],
        $_POST['description'] ?? '',
        $_POST['type'],
        $_POST['start_date'],
        $_POST['due_date']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Görev başarıyla eklendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 