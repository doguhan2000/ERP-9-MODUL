<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        throw new Exception('Geçersiz istek.');
    }

    $id = $data['id'];

    // Fişi sil
    $delete_voucher = "DELETE FROM accounting_vouchers WHERE id = ?";
    $stmt = $conn->prepare($delete_voucher);
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Fiş başarıyla silindi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 