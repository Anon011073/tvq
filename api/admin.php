<?php
// api/admin.php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

try {
    if (isset($input['site_title'])) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'site_title'");
        $stmt->execute([$input['site_title']]);
    }
    if (isset($input['theme'])) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'theme'");
        $stmt->execute([$input['theme']]);
    }
    if (isset($input['plugin_watch'])) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE setting_key = 'plugin_watch'");
        $stmt->execute([$input['plugin_watch']]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
