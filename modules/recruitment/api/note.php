<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "INSERT INTO cv_notes (cv_id, note_text, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            $_POST['cv_id'],
            $_POST['note_text'],
            1 // Şimdilik sabit bir kullanıcı ID'si kullanıyoruz
        ]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
