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
    $name = isset($data['name']) ? trim($data['name']) : '';
    $quantity = isset($data['quantity']) ? floatval($data['quantity']) : 0;
    $price = isset($data['price']) ? floatval($data['price']) : 0;

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
        exit;
    }

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Item name is required']);
        exit;
    }
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }
    
    if ($price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Price must be greater than 0']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $total = $quantity * $price;
    
    // Verify that the item belongs to the current user
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE id = ? AND user_id = ?");
    $stmt->execute([$itemId, $userId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Item not found or access denied']);
        exit;
    }
    
    // Update the item
    $stmt = $pdo->prepare("UPDATE inventory SET name = ?, quantity = ?, price = ?, total = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $quantity, $price, $total, $itemId, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
} catch (PDOException $e) {
    error_log('Database error in edit_item.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('Error in edit_item.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
