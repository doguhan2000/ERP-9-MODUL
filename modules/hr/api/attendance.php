<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// JSON verilerini al
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $employee_id = 1; // TODO: Giriş yapmış kullanıcının ID'si
            $now = date('Y-m-d H:i:s');

            // Vardiyayı bul
            $stmt = $conn->prepare("
                SELECT s.* 
                FROM employee_shifts es
                JOIN shifts s ON es.shift_id = s.id
                WHERE es.employee_id = ? 
                AND es.start_date <= CURRENT_DATE
                AND (es.end_date IS NULL OR es.end_date >= CURRENT_DATE)
            ");
            $stmt->execute([$employee_id]);
            $shift = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data['action'] === 'check_in') {
                // Giriş kaydı oluştur
                $stmt = $conn->prepare("
                    INSERT INTO attendance_records 
                    (employee_id, check_in, shift_id, status, late_minutes) 
                    VALUES (?, ?, ?, ?, ?)
                ");

                $late_minutes = 0;
                $status = 'present';

                if ($shift) {
                    $shift_start = strtotime(date('Y-m-d') . ' ' . $shift['start_time']);
                    $current = strtotime($now);
                    
                    if ($current > $shift_start) {
                        $late_minutes = round(($current - $shift_start) / 60);
                        if ($late_minutes > 0) {
                            $status = 'late';
                        }
                    }
                }

                $stmt->execute([
                    $employee_id,
                    $now,
                    $shift ? $shift['id'] : null,
                    $status,
                    $late_minutes
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Giriş kaydı oluşturuldu'
                ]);

            } elseif ($data['action'] === 'check_out') {
                // En son giriş kaydını bul
                $stmt = $conn->prepare("
                    SELECT * FROM attendance_records 
                    WHERE employee_id = ? 
                    AND DATE(check_in) = CURRENT_DATE
                    AND check_out IS NULL
                    ORDER BY check_in DESC 
                    LIMIT 1
                ");
                $stmt->execute([$employee_id]);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$record) {
                    throw new Exception('Aktif giriş kaydı bulunamadı');
                }

                $early_leave = 0;
                $overtime = 0;

                if ($shift) {
                    $shift_end = strtotime(date('Y-m-d') . ' ' . $shift['end_time']);
                    $current = strtotime($now);

                    if ($current < $shift_end) {
                        $early_leave = round(($shift_end - $current) / 60);
                    } elseif ($current > $shift_end) {
                        $overtime = round(($current - $shift_end) / 60);
                    }
                }

                // Çıkış kaydını güncelle
                $stmt = $conn->prepare("
                    UPDATE attendance_records 
                    SET check_out = ?,
                        early_leave_minutes = ?,
                        overtime_minutes = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $now,
                    $early_leave,
                    $overtime,
                    $record['id']
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Çıkış kaydı oluşturuldu'
                ]);
            }
            break;

        default:
            throw new Exception('Geçersiz istek metodu');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
