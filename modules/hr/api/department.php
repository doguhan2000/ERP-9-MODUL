<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            // Yeni departman ekleme
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $conn->prepare("INSERT INTO departments (name, parent_id, description) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $data['parent_id'] ?: null, $data['description']]);
            
            echo json_encode(['success' => true, 'message' => 'Departman başarıyla eklendi']);
            break;

        case 'PUT':
            // Departman güncelleme
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $conn->prepare("UPDATE departments SET name = ?, parent_id = ?, description = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['parent_id'] ?: null, $data['description'], $data['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Departman başarıyla güncellendi']);
            break;

        case 'DELETE':
            // Departman silme
            $id = $_GET['id'];
            
            // Önce bu departmana bağlı çalışanları kontrol et
            $stmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE department_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Bu departmanda çalışanlar var. Önce çalışanları başka departmana aktarın.');
            }
            
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Departman başarıyla silindi']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
