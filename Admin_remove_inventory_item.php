<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$itemId = $data['item_id'] ?? 0;
$userId = $data['user_id'] ?? 0;

if (!$itemId || !$userId) {
    echo json_encode(['success' => false, 'message' => 'Item ID and User ID are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name FROM inventory WHERE id = ? AND user_id = ?");
    $stmt->execute([$itemId, $userId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found or does not belong to this user']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ? AND user_id = ?");
    $stmt->execute([$itemId, $userId]);

    echo json_encode([
        'success' => true,
        'message' => "Item removed successfully"
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>