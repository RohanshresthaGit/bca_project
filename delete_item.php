<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }
    
    $itemId = isset($data['id']) ? intval($data['id']) : 0;

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // Verify that the item belongs to the current user
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE id = ? AND user_id = ?");
    $stmt->execute([$itemId, $userId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Item not found or access denied']);
        exit;
    }
    
    // Delete the item
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE id = ? AND user_id = ?");
    $stmt->execute([$itemId, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
} catch (PDOException $e) {
    error_log('Database error in delete_item.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('Error in delete_item.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>