<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Durum güncellemesi
        if (isset($_POST['id']) && isset($_POST['status'])) {
            $query = "UPDATE candidates SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_POST['status'], $_POST['id']]);
        } 
        // Yeni aday ekleme
        else {
            $query = "INSERT INTO candidates (
                job_posting_id, first_name, last_name, email, phone,
                experience_years, english_level, expected_salary, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($query);
            $stmt->execute([
                $_POST['job_posting_id'],
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_POST['phone'],
                $_POST['experience_years'],
                $_POST['english_level'],
                $_POST['expected_salary'],
                $_POST['notes'] ?: null
            ]);
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
