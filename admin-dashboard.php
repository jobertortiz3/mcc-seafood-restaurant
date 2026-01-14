<?php
session_start();
// Check for admin session (separate from user session)
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: admin-login.php");
    exit;
}

include 'config.php';
$page_title = 'Admin Dashboard | MCC Seafood Restaurant';

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

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$row = $result->fetch_assoc();
$stats['total_users'] = $row['count'];

// Active reservations (excluding cancelled)
$result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status != 'cancelled'");
$row = $result->fetch_assoc();
$stats['active_reservations'] = $row['count'];

// Cancelled reservations
$result = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'cancelled'");
$row = $result->fetch_assoc();
$stats['cancelled_reservations'] = $row['count'];

// Total gallery images
$result = $conn->query("SELECT COUNT(*) as count FROM gallery");
$row = $result->fetch_assoc();
$stats['total_gallery'] = $row['count'] ?? 0;

// Total tables
$result = $conn->query("SELECT COUNT(*) as count FROM tables");
$row = $result->fetch_assoc();
$stats['total_tables'] = $row['count'];

// Total cottages
$result = $conn->query("SELECT COUNT(*) as count FROM cottages");
$row = $result->fetch_assoc();
$stats['total_cottages'] = $row['count'];

$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_reservation_status') {
        $reservation_id = $_POST['reservation_id'];
        $status = $_POST['status'];
        
        // Admin can only cancel reservations now
        if ($status == 'cancelled') {
            $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $reservation_id);
            if ($stmt->execute()) {
                $message = "Reservation cancelled successfully!";
                logAdminAction($conn, 'cancel_reservation', "Cancelled reservation ID: $reservation_id");
            } else {
                $message = "Error cancelling reservation.";
                logAdminAction($conn, 'cancel_reservation_failed', "Failed to cancel reservation ID: $reservation_id");
            }
        } else {
            $message = "Invalid action. Only cancellation is allowed.";
            logAdminAction($conn, 'invalid_reservation_action', "Attempted invalid status change to: $status for reservation ID: $reservation_id");
        }
    }
    
    if ($action == 'add_menu_item') {
        $name = $_POST['item_name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        
        $image_path = '';
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
            $upload_dir = 'images/menu/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = basename($_FILES['item_image']['name']);
            $image_path = $upload_dir . time() . '_' . $file_name;
            
            if (!move_uploaded_file($_FILES['item_image']['tmp_name'], $image_path)) {
                $image_path = '';
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO menu_items (name, category, price, description, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $category, $price, $description, $image_path);
        if ($stmt->execute()) {
            $message = "Menu item added successfully!";
            logAdminAction($conn, 'add_menu_item', "Added menu item: $name (Category: $category, Price: ₱$price)");
            header("Location: admin-dashboard.php?tab=menu");
            exit;
        } else {
            $message = "Error adding menu item.";
            logAdminAction($conn, 'add_menu_item_failed', "Failed to add menu item: $name");
        }
    }
    
    if ($action == 'edit_menu_item') {
        $item_id = $_POST['item_id'];
        $name = $_POST['item_name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        
        $image_path = $_POST['existing_image'] ?? '';
        if (isset($_FILES['edit_item_image']) && $_FILES['edit_item_image']['error'] == 0) {
            $upload_dir = 'images/menu/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = basename($_FILES['edit_item_image']['name']);
            $image_path = $upload_dir . time() . '_' . $file_name;
            
            if (!move_uploaded_file($_FILES['edit_item_image']['tmp_name'], $image_path)) {
                $image_path = $_POST['existing_image'] ?? '';
            }
        }
        
        $stmt = $conn->prepare("UPDATE menu_items SET name = ?, category = ?, price = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssdssi", $name, $category, $price, $description, $image_path, $item_id);
        if ($stmt->execute()) {
            $message = "Menu item updated successfully!";
        }
    }
    
    if ($action == 'delete_menu_item') {
        $item_id = $_POST['item_id'];
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        if ($stmt->execute()) {
            $message = "Menu item deleted successfully!";
            logAdminAction($conn, 'delete_menu_item', "Deleted menu item ID: $item_id");
        } else {
            $message = "Error deleting menu item.";
            logAdminAction($conn, 'delete_menu_item_failed', "Failed to delete menu item ID: $item_id");
        }
    }
    
    if ($action == 'upload_gallery_image') {
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] == 0) {
            $upload_dir = 'images/gallery/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = basename($_FILES['gallery_image']['name']);
            $file_path = $upload_dir . time() . '_' . $file_name;
            $category = $_POST['gallery_category'];
            
            if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $file_path)) {
                $stmt = $conn->prepare("INSERT INTO gallery (image_name, image_path, category, uploaded_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $file_name, $file_path, $category, $_SESSION['admin_id']);
                if ($stmt->execute()) {
                    $message = "Image uploaded successfully!";
                    logAdminAction($conn, 'upload_gallery_image', "Uploaded gallery image: $file_name (Category: $category)");
                    header("Location: admin-dashboard.php?tab=gallery");
                    exit;
                } else {
                    $message = "Database error while uploading image.";
                    logAdminAction($conn, 'upload_gallery_image_failed', "Database error uploading gallery image: $file_name");
                }
            } else {
                $message = "Error uploading image.";
            }
        } else {
            $message = "No image selected or upload error.";
        }
    }
    
    if ($action == 'delete_gallery_image') {
        $image_id = $_POST['image_id'];
        
        // Get image path first
        $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $image_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $image = $result->fetch_assoc();
        
        if ($image) {
            // Delete file from server
            if (file_exists($image['image_path'])) {
                unlink($image['image_path']);
            }
            
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
            $stmt->bind_param("i", $image_id);
            if ($stmt->execute()) {
                $message = "Image deleted successfully!";
                logAdminAction($conn, 'delete_gallery_image', "Deleted gallery image ID: $image_id");
            } else {
                $message = "Error deleting image from database.";
                logAdminAction($conn, 'delete_gallery_image_failed', "Failed to delete gallery image ID: $image_id from database");
            }
        } else {
            $message = "Image not found.";
        }
    }
    
    if ($action == 'send_user_message') {
        $recipient_id = $_POST['recipient_id'];
        $subject = trim($_POST['subject']);
        $user_message = trim($_POST['message']);
        
        if (!empty($subject) && !empty($user_message) && !empty($recipient_id)) {
            $sender_id = $_SESSION['admin_id'];
            $stmt = $conn->prepare("INSERT INTO user_messages (sender_id, recipient_id, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $sender_id, $recipient_id, $subject, $user_message);
            if ($stmt->execute()) {
                $message = "Message sent successfully to user!";
            } else {
                $message = "Error sending message.";
            }
        } else {
            $message = "Please fill in all fields.";
        }
    }
    
    if ($action == 'delete_message') {
        $message_id = $_POST['message_id'];
        
        if (!empty($message_id)) {
            $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->bind_param("i", $message_id);
            if ($stmt->execute()) {
                $message = "Message deleted successfully!";
                logAdminAction($conn, 'delete_contact_message', "Deleted contact message ID: $message_id");
                header("Location: admin-dashboard.php?tab=messages");
                exit;
            } else {
                $message = "Error deleting message.";
                logAdminAction($conn, 'delete_contact_message_failed', "Failed to delete contact message ID: $message_id");
            }
        } else {
            $message = "Invalid message ID.";
        }
    }
    
    if ($action == 'change_admin_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current admin password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? AND role = 'admin'");
        $stmt->bind_param("i", $_SESSION['admin_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        
        if ($admin && password_verify($current_password, $admin['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'admin'");
                    $stmt->bind_param("si", $hashed_password, $_SESSION['admin_id']);
                    if ($stmt->execute()) {
                        $message = "Password changed successfully!";
                    } else {
                        $message = "Error updating password.";
                    }
                } else {
                    $message = "New password must be at least 6 characters long.";
                }
            } else {
                $message = "New passwords do not match.";
            }
        } else {
            $message = "Current password is incorrect.";
        }
    }

    if ($action == 'add_table') {
        $table_number = trim($_POST['table_number']);
        $capacity = (int)$_POST['capacity'];
        $location = trim($_POST['location']);
        $description = trim($_POST['description']);

        $image_path = '';
        if (isset($_FILES['table_image']) && $_FILES['table_image']['error'] == 0) {
            $upload_dir = 'images/tables/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = basename($_FILES['table_image']['name']);
            $image_path = $upload_dir . time() . '_' . $file_name;

            if (!move_uploaded_file($_FILES['table_image']['tmp_name'], $image_path)) {
                $image_path = '';
            }
        }

        if (!empty($table_number) && $capacity > 0) {
            $stmt = $conn->prepare("INSERT INTO tables (table_number, capacity, location, description, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $table_number, $capacity, $location, $description, $image_path);
            if ($stmt->execute()) {
                $message = "Table added successfully!";
                logAdminAction($conn, 'add_table', "Added table: $table_number (Capacity: $capacity, Location: $location)");
                header("Location: admin-dashboard.php?tab=management");
                exit;
            } else {
                $message = "Error adding table.";
                logAdminAction($conn, 'add_table_failed', "Failed to add table: $table_number");
            }
        } else {
            $message = "Please fill in all required fields.";
        }
    }

    if ($action == 'add_cottage') {
        $cottage_name = trim($_POST['cottage_name']);
        $capacity = trim($_POST['capacity']);
        $description = trim($_POST['description']);
        $amenities = trim($_POST['amenities']);

        $image_path = '';
        if (isset($_FILES['cottage_image']) && $_FILES['cottage_image']['error'] == 0) {
            $upload_dir = 'images/cottages/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = basename($_FILES['cottage_image']['name']);
            $image_path = $upload_dir . time() . '_' . $file_name;

            if (!move_uploaded_file($_FILES['cottage_image']['tmp_name'], $image_path)) {
                $image_path = '';
            }
        }

        if (!empty($cottage_name) && !empty($capacity)) {
            $stmt = $conn->prepare("INSERT INTO cottages (cottage_name, capacity, description, amenities, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $cottage_name, $capacity, $description, $amenities, $image_path);
            if ($stmt->execute()) {
                $message = "Cottage added successfully!";
                logAdminAction($conn, 'add_cottage', "Added cottage: $cottage_name (Capacity: $capacity)");
                header("Location: admin-dashboard.php?tab=management");
                exit;
            } else {
                $message = "Error adding cottage.";
                logAdminAction($conn, 'add_cottage_failed', "Failed to add cottage: $cottage_name");
            }
        } else {
            $message = "Please fill in all required fields.";
        }
    }

    if ($action == 'edit_table') {
        $table_id = (int)$_POST['table_id'];
        $table_number = trim($_POST['table_number']);
        $capacity = (int)$_POST['capacity'];
        $location = trim($_POST['location']);
        $description = trim($_POST['description']);

        $image_path = $_POST['existing_image'] ?? '';
        if (isset($_FILES['edit_table_image']) && $_FILES['edit_table_image']['error'] == 0) {
            $upload_dir = 'images/tables/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = basename($_FILES['edit_table_image']['name']);
            $image_path = $upload_dir . time() . '_' . $file_name;

            if (!move_uploaded_file($_FILES['edit_table_image']['tmp_name'], $image_path)) {
                $image_path = $_POST['existing_image'] ?? '';
            }
        }

        if (!empty($table_number) && $capacity > 0 && $table_id > 0) {
            $stmt = $conn->prepare("UPDATE tables SET table_number = ?, capacity = ?, location = ?, description = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sisssi", $table_number, $capacity, $location, $description, $image_path, $table_id);
            if ($stmt->execute()) {
                $message = "Table updated successfully!";
                logAdminAction($conn, 'edit_table', "Updated table ID: $table_id - $table_number (Capacity: $capacity, Location: $location)");
                header("Location: admin-dashboard.php?tab=management");
                exit;
            } else {
                $message = "Error updating table.";
                logAdminAction($conn, 'edit_table_failed', "Failed to update table ID: $table_id");
            }
        } else {
            $message = "Please fill in all required fields.";
        }
    }

    if ($action == 'edit_cottage') {
        $cottage_id = (int)$_POST['cottage_id'];
        $cottage_name = trim($_POST['cottage_name']);
        $capacity = trim($_POST['capacity']);
        $description = trim($_POST['description']);
        $amenities = trim($_POST['amenities']);

        $image_path = $_POST['existing_image'] ?? '';
        if (isset($_FILES['edit_cottage_image']) && $_FILES['edit_cottage_image']['error'] == 0) {
            $upload_dir = 'images/cottages/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = basename($_FILES['edit_cottage_image']['name']);
            $image_path = $upload_dir . time() . '_' . $file_name;

            if (!move_uploaded_file($_FILES['edit_cottage_image']['tmp_name'], $image_path)) {
                $image_path = $_POST['existing_image'] ?? '';
            }
        }

        if (!empty($cottage_name) && !empty($capacity) && $cottage_id > 0) {
            $stmt = $conn->prepare("UPDATE cottages SET cottage_name = ?, capacity = ?, description = ?, amenities = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $cottage_name, $capacity, $description, $amenities, $image_path, $cottage_id);
            if ($stmt->execute()) {
                $message = "Cottage updated successfully!";
                logAdminAction($conn, 'edit_cottage', "Updated cottage ID: $cottage_id - $cottage_name (Capacity: $capacity)");
                header("Location: admin-dashboard.php?tab=management");
                exit;
            } else {
                $message = "Error updating cottage.";
                logAdminAction($conn, 'edit_cottage_failed', "Failed to update cottage ID: $cottage_id");
            }
        } else {
            $message = "Please fill in all required fields.";
        }
    }
    
    if ($action == 'export_data') {
        $export_type = $_POST['export_type'];
        export_csv($export_type, $conn);
    }
}

function export_csv($table, $conn) {
    if ($table == 'gallery') {
        $result = $conn->query("SELECT g.*, u.username FROM gallery g LEFT JOIN users u ON g.uploaded_by = u.id ORDER BY g.uploaded_at DESC");
    } else {
        $result = $conn->query("SELECT * FROM $table");
    }
    
    $filename = $table . "_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        fputcsv($output, array_keys($row));
        $result->data_seek(0);
    }
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Fetch data
$sql = "SELECT r.*, u.username, 
CASE 
WHEN r.reservation_type = 'table' THEN t.table_number 
WHEN r.reservation_type = 'cottage' THEN c.cottage_name 
END as item_name
FROM reservations r 
LEFT JOIN users u ON r.user_id = u.id 
LEFT JOIN tables t ON r.reservation_type = 'table' AND r.item_id = t.id
LEFT JOIN cottages c ON r.reservation_type = 'cottage' AND r.item_id = c.id
WHERE r.status != 'cancelled'
ORDER BY r.created_at DESC LIMIT 10";
$recent_reservations = $conn->query($sql);

$sql = "SELECT * FROM users WHERE role = 'user' ORDER BY id DESC";
$all_users = $conn->query($sql);

$sql = "SELECT r.*, u.username, 
CASE 
WHEN r.reservation_type = 'table' THEN t.table_number 
WHEN r.reservation_type = 'cottage' THEN c.cottage_name 
END as item_name
FROM reservations r 
LEFT JOIN users u ON r.user_id = u.id 
LEFT JOIN tables t ON r.reservation_type = 'table' AND r.item_id = t.id
LEFT JOIN cottages c ON r.reservation_type = 'cottage' AND r.item_id = c.id
WHERE r.status != 'cancelled'
ORDER BY r.created_at DESC";
$all_reservations = $conn->query($sql);

$sql = "SELECT * FROM menu_items ORDER BY category, name";
$menu_items = $conn->query($sql);

$sql = "SELECT g.*, u.username FROM gallery g LEFT JOIN users u ON g.uploaded_by = u.id ORDER BY g.uploaded_at DESC";
$gallery_images = $conn->query($sql);

$sql = "SELECT cm.*, u.id as user_id FROM contact_messages cm LEFT JOIN users u ON cm.email = u.email ORDER BY cm.created_at DESC";
$contact_messages = $conn->query($sql);

$sql = "SELECT * FROM tables ORDER BY table_number";
$all_tables = $conn->query($sql);

$sql = "SELECT * FROM cottages ORDER BY cottage_name";
$all_cottages = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #2b0000;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-header {
            background: linear-gradient(135deg, #8b0000 0%, #600000 100%);
            color: #fff;
            padding: 30px 0;
            margin-bottom: 30px;
            border-bottom: 4px solid #ffd700;
        }

        .admin-header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
        }

        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #8b0000 0%, #600000 100%);
            color: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            border-left: 5px solid #ffd700;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #ffd700;
        }

        .stat-label {
            font-size: 1rem;
            margin-top: 10px;
            opacity: 0.9;
        }

        .admin-section {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: #333;
        }

        .admin-section h2 {
            color: #8b0000;
            border-bottom: 3px solid #ffd700;
            padding-bottom: 15px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
        }

        .nav-link-custom {
            color: #666 !important;
            border-bottom: 3px solid transparent !important;
            font-weight: bold;
            padding: 10px 20px !important;
            transition: all 0.3s ease;
            background: none !important;
        }

        .nav-link-custom.active {
            color: #8b0000 !important;
            border-bottom-color: #ffd700 !important;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table thead {
            background-color: #8b0000;
            color: #fff;
        }

        .admin-table th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }

        .admin-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }

        .admin-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .category-section {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            background-color: #fafafa;
        }

        .category-title {
            color: #8b0000;
            font-weight: bold;
            margin-bottom: 0;
            border-bottom: 2px solid #ffd700;
            padding-bottom: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-preparing {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-ready {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            width: 100%;
            max-width: 300px;
        }

        .modal-header {
            background-color: #8b0000;
            color: #fff;
        }

        .btn-danger {
            background-color: #8b0000;
            border-color: #8b0000;
        }

        .btn-danger:hover {
            background-color: #a50000;
            border-color: #a50000;
        }

        .form-label {
            color: #8b0000;
            font-weight: bold;
        }

        .form-control:focus {
            border-color: #ffd700;
            box-shadow: 0 0 0 0.2rem rgba(139, 0, 0, 0.25);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .admin-header h1 {
                font-size: 1.8rem;
            }

            .admin-header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .admin-table {
                font-size: 0.8rem;
            }

            .admin-table th, .admin-table td {
                padding: 6px 4px;
            }

            .admin-section {
                padding: 20px 15px;
                margin-bottom: 20px;
            }

            .nav-link-custom {
                padding: 8px 12px !important;
                font-size: 0.9rem;
            }

            .stat-card {
                padding: 20px 15px;
            }

            .stat-number {
                font-size: 2rem;
            }

            .modal-dialog {
                margin: 10px;
                max-width: calc(100vw - 20px);
            }

            .btn-group {
                flex-direction: column;
                gap: 5px;
            }

            .btn-group .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .admin-header h1 {
                font-size: 1.5rem;
            }

            .admin-section h2 {
                font-size: 1.3rem;
            }

            .stat-card {
                padding: 15px 10px;
            }

            .stat-number {
                font-size: 1.8rem;
            }

            .stat-label {
                font-size: 0.9rem;
            }

            .admin-table {
                font-size: 0.75rem;
            }

            .search-box input {
                max-width: 100%;
                padding: 8px 12px;
            }

            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .admin-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="admin-header">
    <div class="container">
        <div class="admin-header-content">
            <div class="header-left">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong></p>
            </div>
            <a href="admin-logout.php" class="btn btn-light" style="color: #8b0000; font-weight: bold;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="container">
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['active_reservations']; ?></div>
            <div class="stat-label">Active Reservations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['cancelled_reservations']; ?></div>
            <div class="stat-label">Cancelled Reservations</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_gallery']; ?></div>
            <div class="stat-label">Gallery Images</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_tables']; ?></div>
            <div class="stat-label">Total Tables</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_cottages']; ?></div>
            <div class="stat-label">Total Cottages</div>
        </div>
    </div>

    <!-- Tabbed Content -->
    <div class="admin-section">
        <ul class="nav nav-tabs nav-tabs-custom" id="adminTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link-custom active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">Overview</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button">Reservations</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button">Cancelled</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">Users</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="management-tab" data-bs-toggle="tab" data-bs-target="#management" type="button">Management</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu" type="button">Menu</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button">Gallery</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button">Messages</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">Activity Logs</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports" type="button">Reports</button>
            </li>
            <li class="nav-item">
                <button class="nav-link-custom" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">Settings</button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview">
                <h2>Recent Reservations</h2>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Guests</th>
                                <th>Table/Cottage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $res_display_id = 1; ?>
                            <?php while ($row = $recent_reservations->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $res_display_id++; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['reservation_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['reservation_time'])); ?></td>
                                    <td><?php echo $row['guests']; ?></td>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_reservation_status">
                                            <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this reservation?')">Cancel Reservation</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reservations Tab -->
            <div class="tab-pane fade" id="reservations">
                <h2>All Reservations</h2>
                <div class="search-box">
                    <input type="text" id="reservationSearch" placeholder="Search reservations..." class="form-control">
                </div>
                <div class="table-responsive">
                    <table class="admin-table" id="reservationsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Guests</th>
                                <th>Table/Cottage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $all_res_display_id = 1; ?>
                            <?php while ($row = $all_reservations->fetch_assoc()): ?>
                                <tr class="res-row">
                                    <td>#<?php echo $all_res_display_id++; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['reservation_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['reservation_time'])); ?></td>
                                    <td><?php echo $row['guests']; ?></td>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_reservation_status">
                                            <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this reservation?')">Cancel Reservation</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="tab-pane fade" id="users">
                <h2>Registered Users</h2>
                <div class="search-box">
                    <input type="text" id="userSearch" placeholder="Search users..." class="form-control">
                </div>
                <div class="table-responsive">
                    <table class="admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $user_display_id = 1; ?>
                            <?php while ($row = $all_users->fetch_assoc()): ?>
                                <tr class="user-row">
                                    <td><?php echo $user_display_id++; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Management Tab -->
            <div class="tab-pane fade" id="management">
                <h2>Restaurant Management</h2>
                <p>Manage tables and cottages for restaurant renovations and expansions.</p>

                <div class="row">
                    <!-- Add Table Section -->
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="mb-0">Add New Table</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="add_table">
                                    <div class="mb-3">
                                        <label for="table_number" class="form-label">Table Number *</label>
                                        <input type="text" class="form-control" id="table_number" name="table_number" required placeholder="e.g., T13, VIP01">
                                    </div>
                                    <div class="mb-3">
                                        <label for="table_capacity" class="form-label">Capacity *</label>
                                        <input type="number" class="form-control" id="table_capacity" name="capacity" required min="1" placeholder="Number of guests">
                                    </div>
                                    <div class="mb-3">
                                        <label for="table_location" class="form-label">Location</label>
                                        <select class="form-control" id="table_location" name="location">
                                            <option value="">Select location</option>
                                            <option value="Indoor">Indoor</option>
                                            <option value="Outdoor">Outdoor</option>
                                            <option value="VIP">VIP Area</option>
                                            <option value="Private">Private Room</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="table_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="table_description" name="description" rows="3" placeholder="Describe the table features, view, etc."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="table_image" class="form-label">Table Photo</label>
                                        <input type="file" class="form-control" id="table_image" name="table_image" accept="image/*">
                                        <small class="form-text text-muted">Upload a photo of the table (optional)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success">Add Table</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Add Cottage Section -->
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="mb-0">Add New Cottage</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="add_cottage">
                                    <div class="mb-3">
                                        <label for="cottage_name" class="form-label">Cottage Name *</label>
                                        <input type="text" class="form-control" id="cottage_name" name="cottage_name" required placeholder="e.g., Sunset Villa, Garden Retreat">
                                    </div>
                                    <div class="mb-3">
                                        <label for="cottage_capacity" class="form-label">Capacity *</label>
                                        <input type="text" class="form-control" id="cottage_capacity" name="capacity" required placeholder="e.g., 10-15, 20-25">
                                    </div>
                                    <div class="mb-3">
                                        <label for="cottage_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="cottage_description" name="description" rows="3" placeholder="Describe the cottage features, views, etc."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cottage_amenities" class="form-label">Amenities</label>
                                        <textarea class="form-control" id="cottage_amenities" name="amenities" rows="3" placeholder="List amenities: WiFi, Kitchen, TV, Jacuzzi, etc."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cottage_image" class="form-label">Cottage Photo</label>
                                        <input type="file" class="form-control" id="cottage_image" name="cottage_image" accept="image/*">
                                        <small class="form-text text-muted">Upload a photo of the cottage (optional)</small>
                                    </div>
                                    <button type="submit" class="btn btn-success">Add Cottage</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Tables and Cottages Overview -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="mb-0">Current Tables (<?php echo $all_tables->num_rows; ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Table</th>
                                                <th>Capacity</th>
                                                <th>Location</th>
                                                <th>Photo</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $tables_result = $conn->query("SELECT id, table_number, capacity, location, description, image FROM tables ORDER BY table_number");
                                            while ($table = $tables_result->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($table['table_number']); ?></td>
                                                    <td><?php echo $table['capacity']; ?></td>
                                                    <td><?php echo htmlspecialchars($table['location']); ?></td>
                                                    <td><?php echo !empty($table['image']) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>'; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editTable(<?php echo $table['id']; ?>, '<?php echo addslashes($table['table_number']); ?>', <?php echo $table['capacity']; ?>, '<?php echo addslashes($table['location']); ?>', '<?php echo addslashes($table['description']); ?>', '<?php echo addslashes($table['image']); ?>')">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card dashboard-card">
                            <div class="card-header">
                                <h5 class="mb-0">Current Cottages (<?php echo $all_cottages->num_rows; ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Cottage</th>
                                                <th>Capacity</th>
                                                <th>Photo</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $cottages_result = $conn->query("SELECT id, cottage_name, capacity, description, amenities, image FROM cottages ORDER BY cottage_name");
                                            while ($cottage = $cottages_result->fetch_assoc()):
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($cottage['cottage_name']); ?></td>
                                                    <td><?php echo $cottage['capacity']; ?></td>
                                                    <td><?php echo !empty($cottage['image']) ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>'; ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editCottage(<?php echo $cottage['id']; ?>, '<?php echo addslashes($cottage['cottage_name']); ?>', '<?php echo addslashes($cottage['capacity']); ?>', '<?php echo addslashes($cottage['description']); ?>', '<?php echo addslashes($cottage['amenities']); ?>', '<?php echo addslashes($cottage['image']); ?>')">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Table Modal -->
            <div class="modal fade" id="editTableModal" tabindex="-1" aria-labelledby="editTableModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTableModalLabel">Edit Table</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit_table">
                                <input type="hidden" name="table_id" id="edit_table_id">
                                <input type="hidden" name="existing_image" id="edit_table_existing_image">
                                
                                <div class="mb-3">
                                    <label for="edit_table_number" class="form-label">Table Number *</label>
                                    <input type="text" class="form-control" id="edit_table_number" name="table_number" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_table_capacity" class="form-label">Capacity *</label>
                                    <input type="number" class="form-control" id="edit_table_capacity" name="capacity" min="1" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_table_location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="edit_table_location" name="location">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_table_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_table_description" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_table_image" class="form-label">Table Image (optional)</label>
                                    <input type="file" class="form-control" id="edit_table_image" name="edit_table_image" accept="image/*">
                                    <small class="form-text text-muted">Leave empty to keep current image</small>
                                    <div id="edit_table_current_image" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Table</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Cottage Modal -->
            <div class="modal fade" id="editCottageModal" tabindex="-1" aria-labelledby="editCottageModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editCottageModalLabel">Edit Cottage</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="edit_cottage">
                                <input type="hidden" name="cottage_id" id="edit_cottage_id">
                                <input type="hidden" name="existing_image" id="edit_cottage_existing_image">
                                
                                <div class="mb-3">
                                    <label for="edit_cottage_name" class="form-label">Cottage Name *</label>
                                    <input type="text" class="form-control" id="edit_cottage_name" name="cottage_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_cottage_capacity" class="form-label">Capacity *</label>
                                    <input type="text" class="form-control" id="edit_cottage_capacity" name="capacity" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_cottage_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_cottage_description" name="description" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_cottage_amenities" class="form-label">Amenities</label>
                                    <textarea class="form-control" id="edit_cottage_amenities" name="amenities" rows="3"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_cottage_image" class="form-label">Cottage Image (optional)</label>
                                    <input type="file" class="form-control" id="edit_cottage_image" name="edit_cottage_image" accept="image/*">
                                    <small class="form-text text-muted">Leave empty to keep current image</small>
                                    <div id="edit_cottage_current_image" class="mt-2"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Update Cottage</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cancelled Reservations Tab -->
            <div class="tab-pane fade" id="cancelled">
                <h2>Cancelled Reservations</h2>
                <div class="search-box">
                    <input type="text" id="cancelledSearch" placeholder="Search cancelled reservations..." class="form-control">
                </div>
                <div class="table-responsive">
                    <table class="admin-table" id="cancelledTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Guests</th>
                                <th>Table/Cottage</th>
                                <th>Status</th>
                                <th>Cancelled Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT r.*, u.username,
                            CASE
                            WHEN r.reservation_type = 'table' THEN t.table_number
                            WHEN r.reservation_type = 'cottage' THEN c.cottage_name
                            END as item_name
                            FROM reservations r
                            LEFT JOIN users u ON r.user_id = u.id
                            LEFT JOIN tables t ON r.reservation_type = 'table' AND r.item_id = t.id
                            LEFT JOIN cottages c ON r.reservation_type = 'cottage' AND r.item_id = c.id
                            WHERE r.status = 'cancelled'
                            ORDER BY r.created_at DESC";
                            $cancelled_reservations = $conn->query($sql);
                            $cancelled_display_id = 1;
                            ?>
                            <?php while ($row = $cancelled_reservations->fetch_assoc()): ?>
                                <tr class="cancelled-row">
                                    <td>#<?php echo $cancelled_display_id++; ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['reservation_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['reservation_time'])); ?></td>
                                    <td><?php echo $row['guests']; ?></td>
                                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Menu Tab -->
            <div class="tab-pane fade" id="menu">
                <h2>Menu Management</h2>
                <button type="button" class="btn btn-danger mb-4" data-bs-toggle="modal" data-bs-target="#addMenuModal">
                    <i class="bi bi-plus-lg"></i> Add New Item
                </button>

                <?php
                // Get unique categories
                $categories = ['Seafood', 'Meat', 'Appetizers', 'Drinks'];
                foreach ($categories as $category):
                    // Get items for this category
                    $category_items = [];
                    mysqli_data_seek($menu_items, 0); // Reset result pointer
                    while ($row = $menu_items->fetch_assoc()) {
                        if ($row['category'] === $category) {
                            $category_items[] = $row;
                        }
                    }
                    if (!empty($category_items)):
                ?>

                <div class="category-section mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="category-title"><?php echo $category; ?> <span class="badge bg-primary"><?php echo count($category_items); ?> items</span></h3>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addMenuModal" onclick="setCategory('<?php echo $category; ?>')">
                            <i class="bi bi-plus-circle"></i> Add <?php echo $category; ?>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $display_id = 1; ?>
                                <?php foreach ($category_items as $item): ?>
                                    <tr>
                                        <td>#<?php echo $display_id++; ?></td>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars(substr($item['description'], 0, 40)); ?><?php echo strlen($item['description']) > 40 ? '...' : ''; ?></td>
                                        <td><?php if ($item['image']): ?><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Menu item" style="max-width: 60px; max-height: 60px;"><?php else: ?>No image<?php endif; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning me-2" onclick="editMenuItem(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', '<?php echo addslashes($item['category']); ?>', <?php echo $item['price']; ?>, '<?php echo addslashes($item['description']); ?>', '<?php echo addslashes($item['image']); ?>')">Edit</button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_menu_item">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this item?');">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                    endif;
                endforeach;
                ?>
            </div>

            <!-- Gallery Tab -->
            <div class="tab-pane fade" id="gallery">
                <h2>Gallery Management</h2>
                
                <!-- Upload Form -->
                <div class="mb-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_gallery_image">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="gallery_image" class="form-label">Upload New Image</label>
                                <input type="file" name="gallery_image" id="gallery_image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-md-4">
                                <label for="gallery_category" class="form-label">Category</label>
                                <select name="gallery_category" id="gallery_category" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="customers">Customers</option>
                                    <option value="MCC family">MCC Family</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Gallery Grid -->
                <?php
                $gallery_images_result = $conn->query("SELECT g.*, u.username FROM gallery g LEFT JOIN users u ON g.uploaded_by = u.id ORDER BY g.uploaded_at DESC");
                ?>
                <div class="row g-4">
                    <?php while ($image = $gallery_images_result->fetch_assoc()): ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($image['image_name']); ?>" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($image['image_name']); ?></h6>
                                    <p class="card-text small text-muted">
                                        Category: <?php echo htmlspecialchars($image['category']); ?><br>
                                        Uploaded by: <?php echo htmlspecialchars($image['username'] ?? 'Admin'); ?><br>
                                        Date: <?php echo date('M d, Y h:i A', strtotime($image['uploaded_at'])); ?>
                                    </p>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                        <input type="hidden" name="action" value="delete_gallery_image">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php if ($gallery_images_result->num_rows == 0): ?>
                    <div class="text-center py-5">
                        <p class="text-muted">No images in gallery yet. Upload some images to get started!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Messages Tab -->
            <div class="tab-pane fade" id="messages">
                <h2>Contact Messages</h2>
                <div class="search-box">
                    <input type="text" id="messageSearch" placeholder="Search messages..." class="form-control">
                </div>
                <div class="table-responsive">
                    <table class="admin-table" id="messagesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $contact_messages->fetch_assoc()): ?>
                                <tr class="msg-row">
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td>
                                        <a href="#" class="text-decoration-none view-message" 
                                           data-id="<?php echo $row['id']; ?>"
                                           data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                           data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                           data-phone="<?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?>"
                                           data-subject="<?php echo htmlspecialchars($row['subject']); ?>"
                                           data-message="<?php echo htmlspecialchars($row['message']); ?>"
                                           data-date="<?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>"
                                           data-user-id="<?php echo $row['user_id'] ?? ''; ?>">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['message'], 0, 50)); ?><?php echo strlen($row['message']) > 50 ? '...' : ''; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm delete-message" 
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                title="Delete Message">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Message Detail Modal -->
                <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="messageModalLabel">Contact Message Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Contact Information</h6>
                                        <p><strong>ID:</strong> <span id="modal-id"></span></p>
                                        <p><strong>Name:</strong> <span id="modal-name"></span></p>
                                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                                        <p><strong>Phone:</strong> <span id="modal-phone"></span></p>
                                        <p><strong>Date:</strong> <span id="modal-date"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Message Details</h6>
                                        <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
                                        <p><strong>Message:</strong></p>
                                        <div id="modal-message" class="border p-3 bg-light rounded" style="min-height: 150px; max-height: 300px; white-space: pre-wrap; overflow-y: auto; word-wrap: break-word; line-height: 1.5;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <div class="btn-group" role="group">
                                    <a id="reply-email" href="#" class="btn btn-primary" target="_blank">Reply via Email</a>
                                    <button id="reply-user" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#replyModal" style="display: none;">Reply via User Message</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Modal -->
            <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="replyModalLabel">Reply to User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="replyForm" method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="action" value="send_user_message">
                                <input type="hidden" id="recipient_id" name="recipient_id">
                                <div class="mb-3">
                                    <label for="reply-subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="reply-subject" name="subject" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reply-message" class="form-label">Message</label>
                                    <textarea class="form-control" id="reply-message" name="message" rows="5" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">This message will be sent to the user's account and they can view it in their dashboard.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reports Tab -->
            <div class="tab-pane fade" id="reports">
                <h2>Export Reports</h2>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Export Reservations</h5>
                                <p class="card-text">Download all reservations as CSV</p>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="export_data">
                                    <input type="hidden" name="export_type" value="reservations">
                                    <button type="submit" class="btn btn-danger">Download CSV</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Export Users</h5>
                                <p class="card-text">Download all users as CSV</p>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="export_data">
                                    <input type="hidden" name="export_type" value="users">
                                    <button type="submit" class="btn btn-danger">Download CSV</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Export Gallery</h5>
                                <p class="card-text">Download gallery images list as CSV</p>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="export_data">
                                    <input type="hidden" name="export_type" value="gallery">
                                    <button type="submit" class="btn btn-danger">Download CSV</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div class="tab-pane fade" id="settings">
                <h2>Account Settings</h2>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Change Password</h5>
                                <p class="card-text">Update your admin account password</p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="change_admin_password">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                        <div class="form-text">Password must be at least 6 characters long.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                    </div>
                                    <button type="submit" class="btn btn-danger">Change Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Tab -->
            <div class="tab-pane fade" id="logs">
                <h2>Activity Logs</h2>
                <p>View all admin activity and system events.</p>

                <div class="search-box mb-3">
                    <input type="text" id="logsSearch" placeholder="Search logs..." class="form-control">
                </div>

                <div class="table-responsive">
                    <table class="admin-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $logs_query = "SELECT l.*, u.username FROM admin_logs l LEFT JOIN users u ON l.admin_id = u.id ORDER BY l.created_at DESC LIMIT 100";
                            $logs_result = $conn->query($logs_query);
                            while ($log = $logs_result->fetch_assoc()):
                            ?>
                                <tr class="log-row">
                                    <td>#<?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['details'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    <td><?php echo date('M d, Y h:i:s A', strtotime($log['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <small class="text-muted">Showing last 100 log entries. Logs are automatically cleaned up periodically.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Menu Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_menu_item">
                    <div class="mb-3">
                        <label for="itemName" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="itemName" name="item_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Seafood">Seafood</option>
                            <option value="Meat">Meat</option>
                            <option value="Appetizers">Appetizers</option>
                            <option value="Desserts">Desserts</option>
                            <option value="Drinks">Drinks</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="itemImage" class="form-label">Item Image (optional)</label>
                        <input type="file" class="form-control" id="itemImage" name="item_image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-danger">Add Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div class="modal fade" id="editMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Menu Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_menu_item">
                    <input type="hidden" name="item_id" id="editItemId">
                    <input type="hidden" name="existing_image" id="editExistingImage">
                    <div class="mb-3">
                        <label for="editItemName" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="editItemName" name="item_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <select class="form-select" id="editCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Seafood">Seafood</option>
                            <option value="Meat">Meat</option>
                            <option value="Appetizers">Appetizers</option>
                            <option value="Desserts">Desserts</option>
                            <option value="Drinks">Drinks</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Price (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editItemImage" class="form-label">Item Image (optional - leave empty to keep current)</label>
                        <input type="file" class="form-control" id="editItemImage" name="edit_item_image" accept="image/*">
                        <div id="currentImageContainer" class="mt-2" style="display: none;">
                            <small class="text-muted">Current image:</small><br>
                            <img id="currentImage" src="" alt="Current" style="max-width: 100px; max-height: 100px;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">Update Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('reservationSearch')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.res-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

document.getElementById('userSearch')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

document.getElementById('messageSearch')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.msg-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

document.getElementById('cancelledSearch')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.cancelled-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

document.getElementById('logsSearch')?.addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.log-row').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
});

// Edit table function
function editTable(id, tableNumber, capacity, location, description, image) {
    document.getElementById('edit_table_id').value = id;
    document.getElementById('edit_table_number').value = tableNumber;
    document.getElementById('edit_table_capacity').value = capacity;
    document.getElementById('edit_table_location').value = location;
    document.getElementById('edit_table_description').value = description;
    document.getElementById('edit_table_existing_image').value = image;
    
    const currentImageContainer = document.getElementById('edit_table_current_image');
    if (image) {
        currentImageContainer.innerHTML = '<img src="' + image + '" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">';
    } else {
        currentImageContainer.innerHTML = '<small class="text-muted">No image uploaded</small>';
    }
    
    // Show the edit modal using Bootstrap 5 native API
    const editModal = new bootstrap.Modal(document.getElementById('editTableModal'));
    editModal.show();
}

// Edit cottage function
function editCottage(id, cottageName, capacity, description, amenities, image) {
    document.getElementById('edit_cottage_id').value = id;
    document.getElementById('edit_cottage_name').value = cottageName;
    document.getElementById('edit_cottage_capacity').value = capacity;
    document.getElementById('edit_cottage_description').value = description;
    document.getElementById('edit_cottage_amenities').value = amenities;
    document.getElementById('edit_cottage_existing_image').value = image;
    
    const currentImageContainer = document.getElementById('edit_cottage_current_image');
    if (image) {
        currentImageContainer.innerHTML = '<img src="' + image + '" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">';
    } else {
        currentImageContainer.innerHTML = '<small class="text-muted">No image uploaded</small>';
    }
    
    // Show the edit modal using Bootstrap 5 native API
    const editModal = new bootstrap.Modal(document.getElementById('editCottageModal'));
    editModal.show();
}
function editMenuItem(id, name, category, price, description, image) {
    document.getElementById('editItemId').value = id;
    document.getElementById('editItemName').value = name;
    document.getElementById('editCategory').value = category;
    document.getElementById('editPrice').value = price;
    document.getElementById('editDescription').value = description;
    document.getElementById('editExistingImage').value = image;
    
    const currentImageContainer = document.getElementById('currentImageContainer');
    const currentImage = document.getElementById('currentImage');
    if (image) {
        currentImage.src = image;
        currentImageContainer.style.display = 'block';
    } else {
        currentImageContainer.style.display = 'none';
    }
    
    // Show the edit modal
    const editModal = new bootstrap.Modal(document.getElementById('editMenuModal'));
    editModal.show();
}

// Set category for add modal
function setCategory(category) {
    document.getElementById('category').value = category;
}

// Handle message modal
document.addEventListener('DOMContentLoaded', function() {
    const messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
    const replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
    
    document.querySelectorAll('.view-message').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get data from clicked element
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const phone = this.getAttribute('data-phone');
            const subject = this.getAttribute('data-subject');
            const message = this.getAttribute('data-message');
            const date = this.getAttribute('data-date');
            const userId = this.getAttribute('data-user-id');
            
            // Populate modal
            document.getElementById('modal-id').textContent = '#' + id;
            document.getElementById('modal-name').textContent = name;
            document.getElementById('modal-email').textContent = email;
            document.getElementById('modal-phone').textContent = phone;
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-message').textContent = message;
            document.getElementById('modal-date').textContent = date;
            
            // Set up reply email link
            const replyLink = document.getElementById('reply-email');
            replyLink.href = 'mailto:' + email + '?subject=Re: ' + subject;
            
            // Set up reply user message
            const replyUserBtn = document.getElementById('reply-user');
            if (userId && userId !== '') {
                document.getElementById('recipient_id').value = userId;
                document.getElementById('reply-subject').value = 'Re: ' + subject;
                replyUserBtn.style.display = 'inline-block';
            } else {
                replyUserBtn.style.display = 'none';
            }
            
            // Show modal
            messageModal.show();
        });
    });
    
    // Handle reply form submission
    document.getElementById('replyForm').addEventListener('submit', function(e) {
        // The form will submit normally, but we want to close the reply modal
        setTimeout(() => {
            replyModal.hide();
        }, 100);
    });
    
    // Handle delete message
    document.querySelectorAll('.delete-message').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const messageId = this.getAttribute('data-id');
            const messageName = this.getAttribute('data-name');
            
            if (confirm(`Are you sure you want to delete the message from "${messageName}"? This action cannot be undone.`)) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_message';
                
                const messageIdInput = document.createElement('input');
                messageIdInput.type = 'hidden';
                messageIdInput.name = 'message_id';
                messageIdInput.value = messageId;
                
                form.appendChild(actionInput);
                form.appendChild(messageIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});

// Activate tab based on URL parameter
const urlParams = new URLSearchParams(window.location.search);
const tab = urlParams.get('tab');
if (tab) {
    const tabElement = document.getElementById(tab + '-tab');
    if (tabElement) {
        tabElement.click();
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
