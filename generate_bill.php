<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$customerName = $data['customerName'] ?? '';
$customerPhone = $data['customerPhone'] ?? '';
$paymentMethod = $data['paymentMethod'] ?? '';
$items = $data['items'] ?? [];

if (empty($customerName) || empty($customerPhone) || empty($paymentMethod) || empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $userId = $_SESSION['user_id'];
    
    // Calculate grand total
    $grandTotal = 0;
    foreach ($items as $item) {
        $grandTotal += $item['quantity'] * $item['price'];
    }
    
    // Insert bill
    $stmt = $pdo->prepare("INSERT INTO bills (user_id, customer_name, customer_phone, payment_method, grand_total) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $customerName, $customerPhone, $paymentMethod, $grandTotal]);
    $billId = $pdo->lastInsertId();
    
    // Insert bill items and update inventory
    foreach ($items as $item) {
        $itemTotal = $item['quantity'] * $item['price'];
        
        // Insert bill item
        $stmt = $pdo->prepare("INSERT INTO bill_items (bill_id, item_name, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$billId, $item['itemName'], $item['quantity'], $item['price'], $itemTotal]);
        
        // Get current inventory item
        $stmt = $pdo->prepare("SELECT quantity, price, total FROM inventory WHERE id = ?");
        $stmt->execute([$item['itemId']]);
        $inventoryItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($inventoryItem) {
            $newQuantity = $inventoryItem['quantity'] - $item['quantity'];
            
            // Prevent negative quantity
            if ($newQuantity < 0) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Insufficient stock for ' . $item['itemName']]);
                exit;
            }
            
            // Calculate new total: subtract the sold amount from current total
            $soldTotal = $item['quantity'] * $inventoryItem['price'];
            $newTotal = $inventoryItem['total'] - $soldTotal;
            
            // Ensure total doesn't go negative
            if ($newTotal < 0) {
                $newTotal = 0;
            }
            
            // Update inventory
            $stmt = $pdo->prepare("UPDATE inventory SET quantity = ?, total = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $newTotal, $item['itemId']]);
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'billId' => $billId]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error generating bill: ' . $e->getMessage()]);
}
?>