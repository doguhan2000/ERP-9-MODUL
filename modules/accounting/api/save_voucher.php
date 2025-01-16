<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['voucher_type']) || !isset($_POST['voucher_date']) || !isset($_POST['amount'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $voucher_type = $_POST['voucher_type'];
    $voucher_date = $_POST['voucher_date'];
    $amount = $_POST['amount'];
    $description = $_POST['description'] ?? '';

    // Fiş numarası oluştur
    $voucher_no = date('Ym') . sprintf('%04d', rand(1, 9999));

    $conn->beginTransaction();

    // Fişi kaydet
    $query = "INSERT INTO accounting_vouchers (
        voucher_no, type, voucher_date, 
        total_amount, description, status, 
        created_at, created_by
    ) VALUES (?, ?, ?, ?, ?, 'posted', CURRENT_TIMESTAMP, 1)";

    $stmt = $conn->prepare($query);
    $stmt->execute([
        $voucher_no, 
        $voucher_type,
        $voucher_date,
        $amount, 
        $description
    ]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Fiş başarıyla kaydedildi.',
        'voucher_no' => $voucher_no
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 