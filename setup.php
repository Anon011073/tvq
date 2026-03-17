<?php
// setup.php

$db_dir = __DIR__ . '/api';
$db_path = $db_dir . '/users.db';

if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

try {
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT
    )");

    echo "✅ SQLite Database initialized. (Note: SQLite uses a file instead of a MySQL server, so no phpMyAdmin setup is required).<br>";
    echo "✅ Table 'users' created successfully.<br>";

    // Insert default admin user
    $username = 'admin';
    $password = 'password123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        echo "✅ Default admin user created (Username: admin, Password: password123).<br>";
    } else {
        echo "ℹ️ Admin user already exists.<br>";
    }

    echo "<br>🚀 Setup complete! You can now <a href='login.php'>Login here</a>.<br>";
    echo "⚠️ For security, please delete this 'setup.php' file from your server after use.";

} catch (PDOException $e) {
    die("❌ Setup failed: " . $e->getMessage());
}
?>
