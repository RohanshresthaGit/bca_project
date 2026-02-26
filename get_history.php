<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get all bills for this user
$stmt = $pdo->prepare("SELECT * FROM bills WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get items for each bill
foreach ($bills as &$bill) {
    $stmt = $pdo->prepare("SELECT * FROM bill_items WHERE bill_id = ?");
    $stmt->execute([$bill['id']]);
    $bill['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($bills);
?>