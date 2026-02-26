<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';

ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$userId = $_GET['user_id'] ?? 0;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

try {
    // Check if is_banned column exists, add it if missing
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER is_admin");
    }

    $stmt = $pdo->prepare("SELECT id, first_name, last_name, address, phone, username, created_at, is_banned FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Ensure is_banned is always set
    $user['is_banned'] = isset($user['is_banned']) ? (int)$user['is_banned'] : 0;

    echo json_encode(['success' => true, 'user' => $user]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}