<?php
header('Content-Type: application/json');
error_reporting(0);

require_once 'config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, username, password, is_admin, is_banned FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Check if user is banned
        if ($user['is_banned'] && !$user['is_admin']) {
            echo json_encode([
                'success' => false,
                'message' => '🚫 Your account has been suspended. Please contact the administrator.'
            ]);
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        echo json_encode([
            'success' => true,
            'username' => $user['username'],
            'is_admin' => $user['is_admin']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>