<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? 0;
$action = $data['action'] ?? '';

if (!$userId || !in_array($action, ['ban', 'unban'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

if ($userId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot ban yourself']);
    exit;
}

try {
    // Auto-add is_banned column if it doesn't exist
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'")->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER is_admin");
    }

    $stmt = $pdo->prepare("SELECT id, is_admin FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    if ($user['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Cannot ban an admin user']);
        exit;
    }

    $isBanned = $action === 'ban' ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
    $stmt->execute([$isBanned, $userId]);

    echo json_encode([
        'success' => true,
        'message' => $action === 'ban' ? 'User has been banned successfully' : 'User has been unbanned successfully'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>