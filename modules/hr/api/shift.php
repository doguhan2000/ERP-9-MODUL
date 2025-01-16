<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['action']) && $_GET['action'] === 'get_shifts') {
                // Vardiya planını takvim için hazırla
                $stmt = $conn->query("
                    SELECT es.*, 
                           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                           s.name as shift_name,
                           s.start_time,
                           s.end_time
                    FROM employee_shifts es
                    JOIN employees e ON es.employee_id = e.id
                    JOIN shifts s ON es.shift_id = s.id
                    WHERE es.start_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                    AND (es.end_date IS NULL OR es.end_date <= DATE_ADD(CURRENT_DATE, INTERVAL 1 MONTH))
                ");
                $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $events = [];
                foreach ($shifts as $shift) {
                    $start_date = new DateTime($shift['start_date']);
                    $end_date = $shift['end_date'] ? new DateTime($shift['end_date']) : clone $start_date;
                    $end_date->modify('+1 day');

                    while ($start_date < $end_date) {
                        $events[] = [
                            'title' => $shift['employee_name'] . ' - ' . $shift['shift_name'],
                            'start' => $start_date->format('Y-m-d') . 'T' . $shift['start_time'],
                            'end' => $start_date->format('Y-m-d') . 'T' . $shift['end_time'],
                            'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))
                        ];
                        $start_date->modify('+1 day');
                    }
                }

                echo json_encode($events);
            }
            break;

        case 'POST':
            // Vardiya ataması yap
            $data = json_decode(file_get_contents('php://input'), true);
            $created_by = 1; // TODO: Giriş yapmış kullanıcının ID'si

            // Çakışan vardiya kontrolü
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM employee_shifts
                WHERE employee_id = ?
                AND (
                    (start_date <= ? AND (end_date IS NULL OR end_date >= ?))
                    OR
                    (start_date <= ? AND (end_date IS NULL OR end_date >= ?))
                )
            ");
            $stmt->execute([
                $data['employee_id'],
                $data['start_date'],
                $data['start_date'],
                $data['end_date'] ?? $data['start_date'],
                $data['end_date'] ?? $data['start_date']
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception('Bu tarih aralığında personelin başka bir vardiya ataması bulunmaktadır');
            }

            // Vardiya atamasını kaydet
            $stmt = $conn->prepare("
                INSERT INTO employee_shifts 
                (employee_id, shift_id, start_date, end_date, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['employee_id'],
                $data['shift_id'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $created_by
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Vardiya ataması yapıldı'
            ]);
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
