<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

try {
    // Auto-add is_banned column if it doesn't exist
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER is_admin");
    }

    // Get all non-admin users
    $stmt = $pdo->query("SELECT id, first_name, last_name, address, phone, username, created_at, COALESCE(is_banned, 0) as is_banned FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>