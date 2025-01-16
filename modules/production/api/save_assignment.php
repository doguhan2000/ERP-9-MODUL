<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['project_id']) || !isset($_POST['employee_id'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $stmt = $conn->prepare("
        INSERT INTO project_assignments (
            project_id, employee_id, estimated_days, 
            start_date, end_date, notes
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['project_id'],
        $_POST['employee_id'],
        $_POST['estimated_days'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['notes'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Personel ataması başarıyla kaydedildi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 