<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if the username or email is already registered
    $sql = "SELECT COUNT(*) AS count FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // determine redirect target
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

    if ($row['count'] > 0) {
        $sep = parse_url($ref, PHP_URL_QUERY) ? '&' : '?';
        header('Location: ' . $ref . $sep . 'auth=register_failed&reason=exists');
        exit;
    } else {
        // Insert the new record
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            $sep = parse_url($ref, PHP_URL_QUERY) ? '&' : '?';
            header('Location: ' . $ref . $sep . 'auth=register_success');
            exit;
        } else {
            $sep = parse_url($ref, PHP_URL_QUERY) ? '&' : '?';
            header('Location: ' . $ref . $sep . 'auth=register_failed&reason=db_error');
            exit;
        }
    }
}
$stmt->close();
$conn->close();
?>
