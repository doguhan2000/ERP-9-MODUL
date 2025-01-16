<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['group_id']) || !isset($_POST['name']) || !isset($_POST['discount_rate'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        UPDATE customer_groups 
        SET 
            name = ?,
            discount_rate = ?,
            description = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['name'],
        $_POST['discount_rate'],
        $_POST['description'] ?? null,
        $_POST['group_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Müşteri grubu başarıyla güncellendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 