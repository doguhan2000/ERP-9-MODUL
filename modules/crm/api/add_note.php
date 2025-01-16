<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['customer_id']) || !isset($_POST['title']) || !isset($_POST['content']) || !isset($_POST['type'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        INSERT INTO notes (customer_id, title, content, type)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['customer_id'],
        $_POST['title'],
        $_POST['content'],
        $_POST['type']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Not başarıyla eklendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 