<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/api/db.php';

// Fetch global settings
$site_title = 'TV Tracker';
$theme = 'default';
$plugin_watch = '0';

try {
    $stmt = $pdo->query("SELECT setting_key, value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['setting_key'] === 'site_title') $site_title = $row['value'];
        if ($row['setting_key'] === 'theme') $theme = $row['value'];
        if ($row['setting_key'] === 'plugin_watch') $plugin_watch = $row['value'];
    }
} catch (Exception $e) {}

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($theme !== 'default'): ?>
        <link rel="stylesheet" href="css/<?php echo htmlspecialchars($theme); ?>.css">
    <?php endif; ?>
    <script>
        window.CURRENT_USER_ID = <?php echo $is_logged_in ? $_SESSION['user_id'] : 'null'; ?>;
    </script>
</head>
<body>
    <header>
        <div class="logo-section">
            <h1 onclick="window.location.href='index.php'"><?php echo htmlspecialchars($site_title); ?></h1>
        </div>
        <nav>
            <a href="index.php">Home</a>
            <a href="movies.php">Movies</a>
            <a href="calendar.php">Calendar</a>
            <?php if ($is_logged_in): ?>
                <a href="favourites.php">Favourites</a>
                <a href="watchlist.php">Watchlist</a>
                <?php if ($plugin_watch === '1'): ?>
                    <a href="watch.php">Watch</a>
                <?php endif; ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
