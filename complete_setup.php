<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'inventory_management';
$username = 'root';
$password = '';

echo "<h2>Database Setup Process</h2>";
echo "<hr>";

// Step 1: Connect to MySQL server
echo "<p><strong>Step 1:</strong> Connecting to MySQL server...</p>";
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to MySQL server successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Connection failed: " . $e->getMessage() . "</p>");
}

// Step 2: Create database if not exists
echo "<p><strong>Step 2:</strong> Creating database '$dbname'...</p>";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database created/verified successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Database creation failed: " . $e->getMessage() . "</p>");
}

// Step 3: Connect to the database
echo "<p><strong>Step 3:</strong> Connecting to database '$dbname'...</p>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Connected to database successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>");
}

// Step 4: Drop existing tables
echo "<p><strong>Step 4:</strong> Removing old tables (if any)...</p>";
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS bill_items");
    $pdo->exec("DROP TABLE IF EXISTS bills");
    $pdo->exec("DROP TABLE IF EXISTS inventory");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p style='color: green;'>✓ Old tables removed successfully!</p>";
} catch(PDOException $e) {
    echo "<p style='color: orange;'>⚠ Warning: " . $e->getMessage() . "</p>";
}

// Step 5: Create users table
echo "<p><strong>Step 5:</strong> Creating users table...</p>";
try {
    $pdo->exec("CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        address VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        is_banned TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Users table created successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Users table creation failed: " . $e->getMessage() . "</p>");
}

// Step 6: Create inventory table
echo "<p><strong>Step 6:</strong> Creating inventory table...</p>";
try {
    $pdo->exec("CREATE TABLE inventory (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Inventory table created successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Inventory table creation failed: " . $e->getMessage() . "</p>");
}

// Step 7: Create bills table
echo "<p><strong>Step 7:</strong> Creating bills table...</p>";
try {
    $pdo->exec("CREATE TABLE bills (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        grand_total DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Bills table created successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Bills table creation failed: " . $e->getMessage() . "</p>");
}

// Step 8: Create bill_items table
echo "<p><strong>Step 8:</strong> Creating bill_items table...</p>";
try {
    $pdo->exec("CREATE TABLE bill_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        bill_id INT NOT NULL,
        item_name VARCHAR(255) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        INDEX idx_bill_id (bill_id),
        FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Bill items table created successfully!</p>";
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Bill items table creation failed: " . $e->getMessage() . "</p>");
}

// Step 9: Create admin user
echo "<p><strong>Step 9:</strong> Creating admin account...</p>";
try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    
    if ($stmt->fetch()) {
        echo "<p style='color: orange;'>⚠ Admin account already exists!</p>";
    } else {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, address, phone, username, password, is_admin) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Admin', 'User', 'Admin Office', '0000000000', 'admin', $hashedPassword, 1]);
        echo "<p style='color: green;'>✓ Admin account created successfully!</p>";
    }
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Admin creation failed: " . $e->getMessage() . "</p>");
}

// Step 10: Verify setup
echo "<p><strong>Step 10:</strong> Verifying setup...</p>";
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && $admin['is_admin'] == 1) {
        echo "<p style='color: green;'>✓ Admin account verified!</p>";
        echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
        echo "<h3 style='color: #2e7d32;'>✓ Setup Complete!</h3>";
        echo "<p><strong>Admin Username:</strong> admin</p>";
        echo "<p><strong>Admin Password:</strong> admin123</p>";
        echo "<p style='margin-top: 20px;'><a href='index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>✗ Admin verification failed!</p>";
    }
    
    // Show all tables
    echo "<p style='margin-top: 20px;'><strong>Created Tables:</strong></p>";
    echo "<ul>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
    foreach($tables as $table) {
        echo "<li>" . $table[0] . "</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    die("<p style='color: red;'>✗ Verification failed: " . $e->getMessage() . "</p>");
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2 {
    color: #333;
}
p {
    line-height: 1.6;
}
</style>