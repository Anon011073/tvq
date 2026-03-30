<?php
// setup.php

// Driver Check
if (!extension_loaded('pdo_sqlite')) {
    die("<h2>Setup Error: Missing Database Driver</h2><p>The <b>pdo_sqlite</b> extension is not enabled in your PHP configuration. <br>To fix this, please find your <b>php.ini</b> file and uncomment (remove the semicolon) from the following line: <br><br><code>extension=pdo_sqlite</code><br><br>Then restart your web server.</p>");
}

$db_dir = __DIR__ . '/api';
$db_path = $db_dir . '/users.db';

if (!is_dir($db_dir)) {
    if (!mkdir($db_dir, 0755, true)) {
        die("❌ Failed to create 'api' directory for the database. Check permissions.");
    }
}

try {
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT,
        is_admin INTEGER DEFAULT 0
    )");

    // Create settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        value TEXT
    )");

    // Insert default settings
    $defaults = [
        'site_title' => 'TV Tracker',
        'theme' => 'default',
        'plugin_watch' => '0',
        'tmdb_api_key' => '0186591f1a581e28945625c27f33d024'
    ];

    foreach ($defaults as $k => $v) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (setting_key, value) VALUES (?, ?)");
        $stmt->execute([$k, $v]);
    }

    echo "✅ SQLite Database initialized. (Note: SQLite is a file-based database, no server required).<br>";
    echo "✅ Tables 'users' and 'settings' created successfully.<br>";

    // Insert default admin user
    $username = 'admin';
    $password = 'admin';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)");
        $stmt->execute([$username, $hashedPassword]);
        echo "✅ Default admin user created (Username: admin, Password: admin).<br>";
    } else {
        echo "ℹ️ Admin user already exists.<br>";
    }

    echo "<br>🚀 Setup complete! You can now <a href='login.php'>Login here</a>.<br>";
    echo "⚠️ For security, please delete this 'setup.php' file from your server after use.";

} catch (PDOException $e) {
    die("❌ Setup failed: " . $e->getMessage());
}
?>
