<?php
/**
 * Migration: Add is_banned column to users table
 * Run this ONCE to update your existing database.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'inventory_management';
$username = 'root';
$password = '';

echo "<h2>Migration: Add Ban Feature</h2><hr>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Connected to database</p>";

    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
    if ($stmt->fetch()) {
        echo "<p style='color:orange'>⚠ Column 'is_banned' already exists. No changes made.</p>";
    } else {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER is_admin");
        echo "<p style='color:green'>✓ Column 'is_banned' added to users table successfully!</p>";
    }

    echo "<div style='background:#e8f5e9;padding:20px;border-radius:8px;margin-top:20px;'>
        <h3 style='color:#2e7d32'>✓ Migration Complete!</h3>
        <p>Your database is now ready for the ban/unban feature.</p>
        <p style='margin-top:15px'><a href='index.php' style='background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to App</a></p>
    </div>";

} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
h2 { color: #333; }
</style>