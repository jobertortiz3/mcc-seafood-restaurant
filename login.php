<?php
session_start();
include 'config.php';

// Allow concurrent sessions - don't redirect if admin is logged in

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // determine redirect target
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();
        
        // Set default role if NULL
        if ($role === null) {
            $role = 'user';
        }
        
        if ($hashed_password !== null && password_verify($password, $hashed_password)) {
            // Allow concurrent sessions - don't clear existing sessions
            // Just set the appropriate session variables based on role
            
            if ($role === 'admin') {
                // Set admin session variables (don't clear user sessions)
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_role'] = 'admin';
                header("Location: admin-dashboard.php");
            } else {
                // Set user session variables (don't clear admin sessions)
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: index.php");
            }
            exit;
        } else {
            $sep = parse_url($ref, PHP_URL_QUERY) ? '&' : '?';
            header('Location: ' . $ref . $sep . 'auth=login_failed&reason=invalid_password');
            exit;
        }
    } else {
        $sep = parse_url($ref, PHP_URL_QUERY) ? '&' : '?';
        header('Location: ' . $ref . $sep . 'auth=login_failed&reason=not_found');
        exit;
    }
} else {
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    $sep = parse_url($ref, PHP_URL_QUERY) ? '&' : '?';
    header('Location: ' . $ref . $sep . 'auth=login_failed&reason=bad_request');
    exit;
}

$stmt->close();
$conn->close();
?>