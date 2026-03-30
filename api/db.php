<?php
// api/db.php

// Check if PDO SQLite driver is enabled
if (!extension_loaded('pdo_sqlite')) {
    die("<h2>Missing Database Driver</h2><p>The <b>pdo_sqlite</b> extension is not enabled in your PHP configuration. <br>To fix this, please find your <b>php.ini</b> file and uncomment (remove the semicolon) from the following line: <br><br><code>extension=pdo_sqlite</code><br><br>Then restart your web server.</p>");
}

$db_path = __DIR__ . '/users.db';
try {
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT,
        is_admin INTEGER DEFAULT 0
    )");

    // Settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        value TEXT
    )");

    // Default settings
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

    // Default Admin (admin / admin)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, is_admin) VALUES ('admin', ?, 1)")
            ->execute([$hash]);
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
