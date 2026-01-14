<?php
session_start();

// Clear user session variables
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['role']);

// Clear admin session variables if they exist
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

session_destroy();
header("Location: index.php");
?>