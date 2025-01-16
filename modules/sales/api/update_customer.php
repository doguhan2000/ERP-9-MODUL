<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['customer_id']) || !isset($_POST['name']) || !isset($_POST['group_id'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        UPDATE customers 
        SET 
            name = ?,
            company_name = ?,
            group_id = ?,
            phone = ?,
            email = ?,
            address = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['name'],
        $_POST['company_name'] ?? null,
        $_POST['group_id'],
        $_POST['phone'] ?? null,
        $_POST['email'] ?? null,
        $_POST['address'] ?? null,
        $_POST['customer_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Müşteri başarıyla güncellendi.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 