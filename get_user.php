<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    // Get all non-admin users
    $stmt = $pdo->query("SELECT id, username, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats for each user
    foreach ($users as &$user) {
        // Total items
        $stmt = $pdo->prepare("SELECT COUNT(*) as item_count FROM inventory WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['item_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['item_count'];
        
        // Total bills
        $stmt = $pdo->prepare("SELECT COUNT(*) as bill_count FROM bills WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $user['bill_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['bill_count'];
    }
    
    echo json_encode($users);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>