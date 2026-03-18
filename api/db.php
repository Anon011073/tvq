<?php
// api/db.php
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

    // Settings table (using setting_key to avoid potential issues)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key TEXT PRIMARY KEY,
        value TEXT
    )");

    // Default settings
    $defaults = [
        'site_title' => 'TV Tracker',
        'theme' => 'default',
        'plugin_watch' => '0'
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
