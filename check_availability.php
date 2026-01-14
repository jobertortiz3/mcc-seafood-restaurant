<?php
header('Content-Type: application/json');
include 'config.php';

$type = $_GET['type'] ?? '';
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';

if (!$type || !$date || !$time) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    $table = $type === 'table' ? 'tables' : 'cottages';
    $id_field = $type === 'table' ? 'table_number' : 'cottage_name';

    // Get all items
    $sql = "SELECT * FROM $table WHERE status = 'available'";
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
        exit;
    }
    $all_items = [];
    while ($row = $result->fetch_assoc()) {
        $all_items[] = $row;
    }

    // Get booked items for this date/time
    $booked_sql = "SELECT item_id FROM reservations WHERE reservation_date = ? AND reservation_time = ? AND reservation_type = ? AND status = 'confirmed'";
    $stmt = $conn->prepare($booked_sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("sss", $date, $time, $type);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Execute failed: ' . $conn->error]);
        exit;
    }
    $booked_result = $stmt->get_result();
    $booked_ids = [];
    while ($row = $booked_result->fetch_assoc()) {
        $booked_ids[] = $row['item_id'];
    }

    // Filter available items
    $available_items = array_filter($all_items, function($item) use ($booked_ids) {
        return !in_array($item['id'], $booked_ids);
    });

    echo json_encode(array_values($available_items));
} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}
?>