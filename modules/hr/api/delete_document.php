<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $file_name = $data['file_name'] ?? '';
    $file_path = '../../../uploads/documents/' . $file_name;

    if (!file_exists($file_path)) {
        throw new Exception('Dosya bulunamadı.');
    }

    if (unlink($file_path)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Dosya silinemedi.');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 