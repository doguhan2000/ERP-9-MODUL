<?php
require_once '../../../config/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['id']) || !isset($_POST['code']) || !isset($_POST['name']) || !isset($_POST['type'])) {
        throw new Exception('Gerekli alanlar eksik.');
    }

    $id = $_POST['id'];
    $code = $_POST['code'];
    $name = $_POST['name'];
    $type = $_POST['type'];

    // Hesap kodu kontrolü (kendi ID'si hariç)
    $check = $conn->prepare("SELECT id FROM account_chart WHERE code = ? AND id != ?");
    $check->execute([$code, $id]);
    if ($check->fetch()) {
        throw new Exception('Bu hesap kodu zaten kullanılıyor.');
    }

    // Hesabı güncelle
    $query = "UPDATE account_chart SET code = ?, name = ?, type = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$code, $name, $type, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Hesap başarıyla güncellendi.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 