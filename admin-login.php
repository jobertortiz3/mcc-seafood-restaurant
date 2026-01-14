<?php
session_start();
include 'config.php';

// Allow concurrent sessions - don't redirect if user is logged in

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

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
        logAdminAction($conn, 'admin_login_failed', "Failed login attempt: empty credentials from IP: " . $_SERVER['REMOTE_ADDR']);
    } else {
        // Fetch user with admin role
        $sql = "SELECT id, username, password FROM users WHERE username = ? AND role = 'admin'";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Allow concurrent sessions - don't clear user session variables
                    
                    // Set admin-specific session variables
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_role'] = 'admin';
                    
                    // Log successful login
                    logAdminAction($conn, 'admin_login', "Admin logged in: $username");
                    
                    // Check if request is from modal (AJAX) or direct page
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'redirect' => 'admin-dashboard.php']);
                    } else {
                        header('Location: admin-dashboard.php');
                    }
                    exit;
                } else {
                    $error = 'Invalid password.';
                    logAdminAction($conn, 'admin_login_failed', "Failed login attempt for username: $username (invalid password)");
                }
            } else {
                $error = 'Admin account not found or no admin privileges.';
                logAdminAction($conn, 'admin_login_failed', "Failed login attempt for username: $username (account not found)");
            }
            $stmt->close();
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    }
    
    // If AJAX request with error, return JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error]);
        exit;
    }
}

$page_title = 'Admin Login | MCC Seafood Restaurant';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #990303ff 0%, #860000ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .login-container h2 {
            color: #8b0000;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .form-control {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #c80404ff 0%, #f50c0cff 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: bold;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #5a1500ff 0%, #653a8a 100%);
            color: white;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Portal</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" class="form-control" name="username" placeholder="Admin Username" required>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <button type="submit" class="btn-login">Login as Admin</button>
        </form>

        <div class="links">
            <p>User Login? <a href="login.php">Click here</a></p>
            <p><a href="index.php">Back to Home</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
