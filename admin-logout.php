<?php
session_start();
include 'config.php';

// Logging functions
function logAdminAction($conn, $action, $details = '') {
    $admin_id = $_SESSION['admin_id'] ?? null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $admin_id, $action, $details, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Log admin logout before clearing session
if (isset($_SESSION['admin_username'])) {
    logAdminAction($conn, 'admin_logout', "Admin logged out: " . $_SESSION['admin_username']);
}

// Clear only admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Redirect to admin login
header('Location: admin-login.php');
exit;
?>
