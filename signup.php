<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON');
    }
    
    $firstName = isset($data['firstName']) ? trim($data['firstName']) : '';
    $lastName = isset($data['lastName']) ? trim($data['lastName']) : '';
    $address = isset($data['address']) ? trim($data['address']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $username = isset($data['username']) ? trim($data['username']) : '';
    $password = isset($data['password']) ? trim($data['password']) : '';

    if (empty($firstName) || empty($lastName) || empty($address) || empty($phone) || empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit;
    }

    // Create user (is_admin defaults to 0)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, address, phone, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$firstName, $lastName, $address, $phone, $username, $hashedPassword]);
    
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['username'] = $username;
    $_SESSION['is_admin'] = 0;
    
    echo json_encode([
        'success' => true, 
        'username' => $username,
        'is_admin' => 0
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>