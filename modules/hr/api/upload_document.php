<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$upload_dir = '../../../uploads/documents/';

try {
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!isset($_FILES['document_file']) || !isset($_POST['employee_id']) || !isset($_POST['document_type'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $file = $_FILES['document_file'];
    $employee_id = $_POST['employee_id'];
    $document_type = $_POST['document_type'];
    $notes = $_POST['notes'] ?? '';

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Dosya yükleme hatası.');
    }

    // Güvenli dosya adı oluştur
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $file_extension;
    
    // Dosyayı yükle
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        // Veritabanına kaydet
        $query = "INSERT INTO employee_documents (employee_id, document_type, file_path, notes) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$employee_id, $document_type, 'uploads/documents/' . $new_filename, $notes]);

        echo json_encode([
            'success' => true,
            'message' => 'Belge başarıyla yüklendi.'
        ]);
    } else {
        throw new Exception('Dosya yüklenemedi.');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 