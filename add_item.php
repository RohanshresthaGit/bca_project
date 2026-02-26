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
    
    $name = isset($data['name']) ? trim($data['name']) : '';
    $quantity = isset($data['quantity']) ? floatval($data['quantity']) : 0;
    $price = isset($data['price']) ? floatval($data['price']) : 0;

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
    
    // First verify the table structure
    try {
        $pdo->query("SELECT user_id FROM inventory LIMIT 1");
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database table structure error. Please run setup_database.php first.']);
        exit;
    }
    
    // Check if item with same name and price exists
    $stmt = $pdo->prepare("SELECT id, quantity, total FROM inventory WHERE user_id = ? AND name = ? AND price = ?");
    $stmt->execute([$userId, $name, $price]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        // Update existing item - add quantities and totals
        $newQuantity = $existingItem['quantity'] + $quantity;
        $newTotal = $existingItem['total'] + $total;
        
        $stmt = $pdo->prepare("UPDATE inventory SET quantity = ?, total = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $newTotal, $existingItem['id']]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO inventory (user_id, name, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $quantity, $price, $total]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Item added successfully']);
} catch (PDOException $e) {
    error_log('Database error in add_item.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('Error in add_item.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>