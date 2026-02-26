<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM inventory WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($inventory);
?>