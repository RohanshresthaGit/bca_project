<?php
session_start();
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        h2 { color: #333; }
        .box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .ok { color: green; font-weight: bold; }
        .fail { color: red; font-weight: bold; }
        .warn { color: orange; font-weight: bold; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 0.85rem; }
        button { padding: 10px 20px; background: #2E6B46; color: white; border: none; border-radius: 6px; cursor: pointer; margin: 5px; font-size: 1rem; }
        button:hover { background: #17402A; }
        input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin: 5px; }
        #results { margin-top: 15px; }
    </style>
</head>
<body>
<h2>🔧 Admin Debug Tool</h2>

<!-- SESSION CHECK -->
<div class="box">
    <h3>1. Session Status</h3>
    <?php
    echo "<p>Session ID: <code>" . session_id() . "</code></p>";
    echo "<p>user_id: ";
    if (isset($_SESSION['user_id'])) {
        echo "<span class='ok'>✓ SET = " . $_SESSION['user_id'] . "</span>";
    } else {
        echo "<span class='fail'>✗ NOT SET — You are not logged in!</span>";
    }
    echo "</p>";

    echo "<p>is_admin: ";
    if (isset($_SESSION['is_admin'])) {
        if ($_SESSION['is_admin'] == 1) {
            echo "<span class='ok'>✓ SET = 1 (Admin)</span>";
        } else {
            echo "<span class='fail'>✗ SET = 0 (NOT Admin — logged in as regular user!)</span>";
        }
    } else {
        echo "<span class='fail'>✗ NOT SET</span>";
    }
    echo "</p>";

    echo "<p>username: ";
    if (isset($_SESSION['username'])) {
        echo "<span class='ok'>✓ SET = " . htmlspecialchars($_SESSION['username']) . "</span>";
    } else {
        echo "<span class='warn'>⚠ NOT SET</span>";
    }
    echo "</p>";
    ?>
</div>

<!-- DATABASE CHECK -->
<div class="box">
    <h3>2. Database & Column Check</h3>
    <?php
    $host = 'localhost'; $dbname = 'inventory_management'; $dbuser = 'root'; $dbpass = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='ok'>✓ Database connected</p>";

        // Check is_banned column
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
        if ($stmt->fetch()) {
            echo "<p class='ok'>✓ is_banned column EXISTS</p>";
        } else {
            echo "<p class='fail'>✗ is_banned column MISSING — run Add_ban_column.php!</p>";
        }

        // Show users
        $stmt = $pdo->query("SELECT id, username, is_admin, is_banned FROM users ORDER BY id");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Users in database:</p><pre>";
        foreach ($users as $u) {
            echo "ID:{$u['id']} | username:{$u['username']} | is_admin:{$u['is_admin']} | is_banned:{$u['is_banned']}\n";
        }
        echo "</pre>";

        // Get first non-admin user ID for testing
        $testUser = null;
        foreach ($users as $u) {
            if ($u['is_admin'] == 0) { $testUser = $u; break; }
        }

    } catch (Exception $e) {
        echo "<p class='fail'>✗ DB Error: " . $e->getMessage() . "</p>";
    }
    ?>
</div>

<!-- LIVE API TESTS -->
<div class="box">
    <h3>3. Live API Tests (run from browser)</h3>
    <p>Click each button to test the endpoint directly:</p>

    <button onclick="testEndpoint('admin_get_users.php', 'GET', null, 'r1')">Test admin_get_users.php</button>
    <div id="r1" style="margin-top:10px;"></div>

    <hr style="margin:15px 0;">
    <label>User ID to test: <input type="number" id="testUserId" value="<?= $testUser ? $testUser['id'] : '' ?>" style="width:80px;"></label>
    <button onclick="testEndpoint('admin_get_user_details.php?user_id='+document.getElementById('testUserId').value, 'GET', null, 'r2')">Test admin_get_user_details.php</button>
    <div id="r2" style="margin-top:10px;"></div>

    <hr style="margin:15px 0;">
    <button onclick="testBan()">Test Admin_ban_user_.php (ban user above)</button>
    <div id="r3" style="margin-top:10px;"></div>

    <hr style="margin:15px 0;">
    <button onclick="testEndpoint('admin_get_user_Inventory.php?user_id='+document.getElementById('testUserId').value, 'GET', null, 'r4')">Test admin_get_user_Inventory.php</button>
    <div id="r4" style="margin-top:10px;"></div>
</div>

<script>
async function testEndpoint(url, method, body, resultId) {
    const el = document.getElementById(resultId);
    el.innerHTML = '<em>Loading...</em>';
    try {
        const opts = { method, headers: {'Content-Type': 'application/json'} };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        const text = await res.text();
        let display = text;
        try {
            display = JSON.stringify(JSON.parse(text), null, 2);
        } catch(e) {}
        const color = text.includes('"success":true') || (text.startsWith('[') && !text.includes('"success":false')) ? 'green' : 'red';
        el.innerHTML = `<pre style="border-left:4px solid ${color}; padding-left:10px;">${escapeHtml(display)}</pre>`;
    } catch(e) {
        el.innerHTML = `<pre style="border-left:4px solid red; padding-left:10px;">ERROR: ${e.message}</pre>`;
    }
}

async function testBan() {
    const userId = document.getElementById('testUserId').value;
    await testEndpoint('Admin_ban_user_.php', 'POST', {user_id: parseInt(userId), action: 'ban'}, 'r3');
}

function escapeHtml(text) {
    return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>

</body>
</html>