<?php
session_start();
// Check for user session - allow concurrent admin and user sessions
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error_message = '';

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'mark_as_read' && isset($_POST['message_id'])) {
        $message_id = $_POST['message_id'];
        $stmt = $conn->prepare("UPDATE user_messages SET is_read = 1 WHERE id = ? AND recipient_id = ?");
        $stmt->bind_param("ii", $message_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php#messages");
        exit;
    }

    if ($action == 'delete_account') {
        // Start transaction for safe deletion
        $conn->begin_transaction();

        try {
            // Delete user's reservations
            $stmt = $conn->prepare("DELETE FROM reservations WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Delete user's orders
            $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Delete user's messages (both sent and received)
            $stmt = $conn->prepare("DELETE FROM user_messages WHERE sender_id = ? OR recipient_id = ?");
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $stmt->close();

            // Delete user's gallery images
            $stmt = $conn->prepare("DELETE FROM gallery WHERE uploaded_by = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Finally delete the user account
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();

            // Destroy session and redirect
            session_destroy();
            header("Location: index.php?message=account_deleted");
            exit;

        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error_message = "Error deleting account. Please try again.";
        }
    }
}

if (isset($_GET['message']) && $_GET['message'] == 'account_deleted') {
    $message = "Your account has been successfully deleted.";
}

$page_title = 'Dashboard | MCC Seafood Restaurant';
include 'header.php';

// Fetch user's reservations
$sql = "SELECT r.* 
FROM reservations r 
WHERE r.user_id = ? ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user's messages
$sql = "SELECT um.*, u.username as sender_name FROM user_messages um 
        LEFT JOIN users u ON um.sender_id = u.id 
        WHERE um.recipient_id = ? ORDER BY um.created_at DESC";
$stmt_messages = $conn->prepare($sql);
$stmt_messages->bind_param("i", $user_id);
$stmt_messages->execute();
$messages_result = $stmt_messages->get_result();
?>

<style>
/* DASHBOARD */
.dashboard-header {
    background-color: #8b0000;
    color: #fff;
    padding: 30px 0;
    margin-bottom: 30px;
}
.dashboard-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.status-confirmed { color: #28a745; }
.status-cancelled { color: #dc3545; }

/* Section separators */
.dashboard-section {
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 2rem;
    margin-bottom: 2rem;
}
.dashboard-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

/* RESPONSIVE DESIGN FOR DASHBOARD */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 20px 0;
        text-align: center;
    }

    .dashboard-header h1 {
        font-size: 1.8rem;
    }

    .dashboard-header p {
        font-size: 1rem;
    }

    .dashboard-card {
        margin-bottom: 15px;
    }

    .dashboard-card .card-body {
        padding: 15px;
    }

    .dashboard-card .card-title {
        font-size: 1.1rem;
    }

    .card-text {
        font-size: 0.9rem;
    }

    .btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .d-flex {
        flex-direction: column;
        gap: 10px;
    }

    .justify-content-between {
        justify-content: center !important;
    }

    .text-end {
        text-align: center !important;
    }

    .dashboard-section {
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .alert {
        font-size: 0.9rem;
        padding: 12px 15px;
    }
}

@media (max-width: 480px) {
    .dashboard-header h1 {
        font-size: 1.5rem;
    }

    .dashboard-card .card-title {
        font-size: 1rem;
    }

    .card-text {
        font-size: 0.85rem;
    }

    .card-text br {
        display: none;
    }

    .card-text strong {
        display: block;
        margin-bottom: 2px;
    }

    .btn-reserve {
        width: 100%;
        padding: 10px;
    }
}
</style>

<div class="dashboard-header">
    <div class="container">
        <h1>Welcome to Your Dashboard, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Manage your reservations and account information.</p>
    </div>
</div>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Reservations Section -->
    <div class="row mb-5 dashboard-section">
        <div class="col-12">
            <h3>Your Reservations</h3>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo ucfirst($row['reservation_type']); ?> Reservation on <?php echo date('F j, Y', strtotime($row['reservation_date'])); ?> at <?php echo date('g:i A', strtotime($row['reservation_time'])); ?></h5>
                            <p class="card-text">
                                <strong>Name:</strong> <?php echo htmlspecialchars($row['full_name']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
                                <strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?><br>
                                <strong>Guests:</strong> <?php echo $row['guests']; ?><br>
                                <strong>Special Requests:</strong> <?php echo htmlspecialchars($row['special_requests'] ?: 'None'); ?><br>
                                <strong>Status:</strong> <span class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span><br>
                                <small class="text-muted">Submitted on <?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?></small>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <p>You have no reservations yet.</p>
                        <a href="reserve.php" class="btn btn-reserve">Make a Reservation</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Messages Section -->
    <div class="row mb-5 dashboard-section">
        <div class="col-12">
            <h3 id="messages">Your Messages</h3>
            <?php if ($messages_result->num_rows > 0): ?>
                <?php while ($msg = $messages_result->fetch_assoc()): ?>
                    <div class="card dashboard-card <?php echo $msg['is_read'] ? '' : 'border-primary'; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="card-title mb-2"><?php echo htmlspecialchars($msg['subject']); ?></h6>
                                <?php if (!$msg['is_read']): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </div>
                            <p class="card-text mb-2"><?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?><?php echo strlen($msg['message']) > 100 ? '...' : ''; ?></p>
                            <?php if (strlen($msg['message']) > 100): ?>
                                <button class="btn btn-sm btn-outline-primary mb-2" onclick="toggleMessage(this, <?php echo $msg['id']; ?>)">Read More</button>
                                <div class="full-message" style="display: none;">
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">From: <?php echo htmlspecialchars($msg['sender_name'] ?? 'Admin'); ?> on <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?></small>
                                <?php if (!$msg['is_read']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_as_read">
                                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <p>You have no messages yet.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row">
        <div class="col-12">
            <h3>Quick Actions</h3>
            <div class="list-group">
                <a href="reserve.php" class="list-group-item list-group-item-action">Make New Reservation</a>
                <a href="menu.php" class="list-group-item list-group-item-action">View Menu</a>
                <a href="contact.php" class="list-group-item list-group-item-action">Contact Us</a>
                <a href="logout.php" class="list-group-item list-group-item-action">Logout</a>
            </div>

            <!-- Account Management Section -->
            <h3 class="mt-4">Account Management</h3>
            <div class="card dashboard-card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Danger Zone</h5>
                    <p class="card-text">Once you delete your account, there is no going back. This will permanently delete your account and remove all your data from our servers.</p>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title text-danger" id="deleteAccountModalLabel">Delete Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p class="text-danger fw-bold">⚠️ This action cannot be undone!</p>
            <p>Are you sure you want to delete your account? This will permanently remove:</p>
            <ul>
                <li>Your account and profile information</li>
                <li>All your reservations</li>
                <li>Your order history</li>
                <li>All messages</li>
                <li>Any images you've uploaded</li>
            </ul>
            <p class="text-muted">Please type <strong>"DELETE"</strong> to confirm:</p>
            <input type="text" id="deleteConfirmation" class="form-control" placeholder="Type DELETE to confirm">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn" disabled onclick="deleteAccount()">
                Delete Account
            </button>
        </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleMessage(button, messageId) {
    const fullMessage = button.nextElementSibling;
    if (fullMessage.style.display === 'none' || fullMessage.style.display === '') {
        fullMessage.style.display = 'block';
        button.textContent = 'Read Less';
    } else {
        fullMessage.style.display = 'none';
        button.textContent = 'Read More';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Mark messages as read when clicked
    document.querySelectorAll('.card').forEach(card => {
        if (card.classList.contains('border-primary')) {
            card.addEventListener('click', function() {
                // You could add AJAX call here to mark as read
                // For now, just remove the visual indicator
                this.classList.remove('border-primary');
                const badge = this.querySelector('.badge');
                if (badge) badge.remove();
            });
        }
    });

    // Handle delete confirmation input
    const deleteConfirmation = document.getElementById('deleteConfirmation');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    if (deleteConfirmation && confirmDeleteBtn) {
        deleteConfirmation.addEventListener('input', function() {
            confirmDeleteBtn.disabled = this.value !== 'DELETE';
        });
    }
});

function deleteAccount() {
    if (confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_account';

        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</body>
</html>

<?php
$stmt->close();
$stmt_messages->close();
$conn->close();
?>