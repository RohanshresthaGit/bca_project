<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Admin user already exists'
        ]);
        exit;
    }
    
    // Create admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, address, phone, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'Admin',
        'User',
        'Admin Office',
        '0000000000',
        'admin',
        $hashedPassword,
        1
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin user created successfully!',
        'username' => 'admin',
        'password' => 'admin123'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error creating admin: ' . $e->getMessage()
    ]);
}
?>