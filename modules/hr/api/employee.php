<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT e.*, 
                    d.name as department_name, 
                    p.title as position_title,
                    CONCAT(m.first_name, ' ', m.last_name) as manager_name
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN positions p ON e.position_id = p.id
                    LEFT JOIN employees m ON e.manager_id = m.id
                    WHERE e.id = ?");
                $stmt->execute([$_GET['id']]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($employee) {
                    echo json_encode(['success' => true, 'data' => $employee]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Çalışan bulunamadı']);
                }
            }
            break;

        case 'POST':
            $photo_path = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $photo_path = uploadPhoto($_FILES['photo']);
            }

            // Otomatik personel numarası oluştur
            $employee_no = 'EMP' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO employees (
                employee_no, first_name, last_name, email, phone, photo, 
                department_id, position_id, manager_id, hire_date, salary, status
            ) VALUES (
                :employee_no, :first_name, :last_name, :email, :phone, :photo,
                :department_id, :position_id, :manager_id, :hire_date, :salary, 'active'
            )";
            
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                'employee_no' => $employee_no,
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'photo' => $photo_path,
                'department_id' => $_POST['department_id'],
                'position_id' => $_POST['position_id'],
                'manager_id' => $_POST['manager_id'] ?? null,
                'hire_date' => $_POST['hire_date'],
                'salary' => $_POST['salary']
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Personel başarıyla eklendi']);
            } else {
                throw new Exception("Personel eklenirken bir hata oluştu");
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['id'])) {
                $stmt = $conn->prepare("UPDATE employees SET 
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    department_id = ?,
                    position_id = ?,
                    manager_id = ?,
                    hire_date = ?,
                    salary = ?
                    WHERE id = ?");
                
                $result = $stmt->execute([
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $data['phone'],
                    $data['department_id'],
                    $data['position_id'],
                    $data['manager_id'] ?? null,
                    $data['hire_date'],
                    $data['salary'],
                    $data['id']
                ]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Personel başarıyla güncellendi']);
                } else {
                    throw new Exception("Personel güncellenirken bir hata oluştu");
                }
            }
            break;

        case 'PATCH':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['id'])) {
                // Mevcut durumu kontrol et
                $stmt = $conn->prepare("SELECT status FROM employees WHERE id = ?");
                $stmt->execute([$data['id']]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$current) {
                    throw new Exception("Personel bulunamadı");
                }

                // Durumu değiştir
                $new_status = $current['status'] == 'active' ? 'passive' : 'active';
                
                $stmt = $conn->prepare("UPDATE employees SET status = ? WHERE id = ?");
                $result = $stmt->execute([$new_status, $data['id']]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Personel durumu güncellendi']);
                } else {
                    throw new Exception("Personel durumu güncellenirken bir hata oluştu");
                }
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                // Önce personelin fotoğrafını sil
                $stmt = $conn->prepare("SELECT photo FROM employees WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $employee = $stmt->fetch();
                
                if ($employee && $employee['photo']) {
                    $photo_path = "../uploads/" . $employee['photo'];
                    if (file_exists($photo_path)) {
                        unlink($photo_path);
                    }
                }

                $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
                $result = $stmt->execute([$_GET['id']]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Personel başarıyla silindi']);
                } else {
                    throw new Exception("Personel silinirken bir hata oluştu");
                }
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function uploadPhoto($file) {
    $upload_dir = '../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('Geçersiz dosya formatı. Sadece JPG, JPEG ve PNG dosyaları yüklenebilir.');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB
        throw new Exception('Dosya boyutu çok büyük. Maksimum 5MB yüklenebilir.');
    }
    
    $photo = uniqid() . '.' . $file_extension;
    move_uploaded_file($file['tmp_name'], $upload_dir . $photo);
    
    return $photo;
}
