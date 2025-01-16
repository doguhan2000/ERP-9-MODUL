<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['code']) || !isset($_POST['name']) || !isset($_POST['type'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $code = $_POST['code'];
    $name = $_POST['name'];
    $type = $_POST['type'];

    // Hesap kodu kontrolü
    $check = $conn->prepare("SELECT id FROM account_chart WHERE code = ?");
    $check->execute([$code]);
    if ($check->fetch()) {
        throw new Exception('Bu hesap kodu zaten kullanılıyor.');
    }

    // Yeni hesabı kaydet
    $query = "INSERT INTO account_chart (code, name, type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$code, $name, $type]);

    echo json_encode([
        'success' => true,
        'message' => 'Hesap başarıyla eklendi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 