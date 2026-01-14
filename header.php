<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<meta name="description" content="MCC Seafood Restaurant - Fresh from the sea, cooked with passion">
<meta name="keywords" content="seafood, restaurant, reservations, dining">
<meta name="author" content="MCC Seafood Restaurant">
<title><?php echo isset($page_title) ? $page_title : 'MCC Seafood Restaurant'; ?></title>

<?php include_once 'config.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #2b0000;
    color: #fff;
}

/* NAVBAR */
.navbar {
    background-color: #8b0000;
    padding: 18px 0;
}
.navbar-brand {
    font-size: 1.6rem;
    font-weight: bold;
}
.navbar-brand img {
    height: 55px;
    margin-right: 12px;
}
.navbar-nav .nav-link {
    font-size: 1.1rem;
    font-weight: bold;
    color: #fff;
    margin-left: 12px;
}
.navbar-nav .nav-link:hover {
    color: #ffd700;
}
.navbar-nav .nav-link.active {
    color: #ffd700;
    border-bottom: 3px solid #ffd700;
    padding-bottom: 15px;
}
.navbar-nav .dropdown-item .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
.navbar-nav .dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* BUTTON */
.btn-reserve {
    background-color: #ffd700;
    color: #8b0000;
    font-weight: bold;
}
.btn-reserve:hover {
    background-color: #ffcc00;
}

/* BOOK NOW BUTTON */
.book-now-btn {
    font-size: 1.2rem !important;
    font-weight: bold !important;
    padding: 10px 15px !important;
}

/* RESPONSIVE DESIGN */
@media (max-width: 1200px) {
    .navbar-brand {
        font-size: 1.4rem;
    }
    .navbar-brand img {
        height: 45px;
    }
}

@media (max-width: 992px) {
    .navbar-brand {
        font-size: 1.3rem;
    }
    .navbar-brand img {
        height: 40px;
        margin-right: 8px;
    }
    .navbar-nav .nav-link {
        font-size: 1rem;
        margin-left: 8px;
        padding: 8px 12px;
    }
    .book-now-btn {
        font-size: 1rem !important;
        padding: 8px 12px !important;
    }
}

@media (max-width: 768px) {
    .navbar {
        padding: 12px 0;
    }
    .navbar-brand {
        font-size: 1.2rem;
    }
    .navbar-brand img {
        height: 35px;
        margin-right: 6px;
    }
    .navbar-nav .nav-link {
        font-size: 0.95rem;
        margin-left: 0;
        padding: 6px 10px;
    }
    .book-now-btn {
        font-size: 0.9rem !important;
        padding: 6px 10px !important;
    }
    .navbar-nav .dropdown-item {
        padding: 8px 16px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .navbar-brand {
        font-size: 1.1rem;
    }
    .navbar-brand img {
        height: 30px;
        margin-right: 5px;
    }
    .navbar-nav .nav-link {
        font-size: 0.9rem;
        padding: 5px 8px;
    }
    .book-now-btn {
        font-size: 0.85rem !important;
        padding: 5px 8px !important;
    }
    .navbar-toggler {
        padding: 4px 6px;
    }
    .navbar-toggler-icon {
        width: 18px;
        height: 18px;
    }
}

/* MOBILE OPTIMIZATIONS */
@media (max-width: 768px) {
    body {
        font-size: 14px;
    }

    .container {
        padding-left: 15px;
        padding-right: 15px;
    }

    h1 {
        font-size: 2rem;
    }

    h2 {
        font-size: 1.5rem;
    }

    h3 {
        font-size: 1.25rem;
    }

    h4 {
        font-size: 1.1rem;
    }

    h5 {
        font-size: 1rem;
    }

    .btn {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .btn-lg {
        padding: 10px 20px;
        font-size: 1rem;
    }

    .form-control {
        font-size: 16px; /* Prevents zoom on iOS */
    }

    .table-responsive {
        font-size: 0.85rem;
    }

    .modal-dialog {
        margin: 10px;
    }

    .modal-content {
        border-radius: 8px;
    }

    .card {
        margin-bottom: 15px;
    }

    .alert {
        padding: 12px 15px;
        font-size: 0.9rem;
    }
}

/* TABLET OPTIMIZATIONS */
@media (min-width: 769px) and (max-width: 1024px) {
    .container {
        max-width: 90%;
    }

    .table-responsive {
        font-size: 0.9rem;
    }

    .modal-dialog {
        max-width: 80%;
    }
}

/* LARGE DESKTOP OPTIMIZATIONS */
@media (min-width: 1400px) {
    .container {
        max-width: 1300px;
    }
}

/* TOUCH OPTIMIZATIONS */
@media (hover: none) and (pointer: coarse) {
    .navbar-nav .nav-link {
        padding: 12px 16px;
        min-height: 44px;
        display: flex;
        align-items: center;
    }

    .dropdown-item {
        padding: 12px 16px;
        min-height: 44px;
        display: flex;
        align-items: center;
    }

    .btn {
        min-height: 44px;
        padding: 12px 24px;
    }

    .form-control {
        min-height: 44px;
        padding: 12px 16px;
    }

    .table td, .table th {
        padding: 12px 8px;
    }
}

/* HIGH RESOLUTION DISPLAYS */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .navbar-brand img {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}

/* PRINT STYLES */
@media print {
    .navbar,
    .btn,
    .modal,
    .carousel-control-prev,
    .carousel-control-next {
        display: none !important;
    }

    body {
        background: white !important;
        color: black !important;
    }

    .container {
        max-width: none;
        padding: 0;
    }
}

/* ACCESSIBILITY IMPROVEMENTS */
@media (prefers-reduced-motion: reduce) {
    .carousel,
    .modal,
    .collapse {
        transition: none !important;
    }

    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* DARK MODE SUPPORT */
@media (prefers-color-scheme: dark) {
    .modal-content,
    .modal-body {
        background-color: #2b0000 !important;
        color: #fff !important;
    }

    .form-control {
        background-color: #fff !important;
        color: #000 !important;
        border: 1px solid #dee2e6;
    }
}

/* FLEXIBLE IMAGES */
img {
    max-width: 100%;
    height: auto;
}

/* RESPONSIVE UTILITIES */
.d-flex-responsive {
    display: flex;
}

@media (max-width: 768px) {
    .d-flex-responsive {
        flex-direction: column;
    }

    .text-center-mobile {
        text-align: center;
    }

    .mb-3-mobile {
        margin-bottom: 1rem;
    }
}

/* ADMIN DASHBOARD RESPONSIVE */
@media (max-width: 768px) {
    .admin-dashboard .stats-grid {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }

    .admin-table {
        font-size: 0.8rem;
    }

    .admin-table th,
    .admin-table td {
        padding: 8px 4px;
    }

    .tab-content {
        padding: 15px 10px;
    }

    .modal-xl {
        max-width: 95% !important;
    }
}

/* RESERVATION SYSTEM RESPONSIVE */
@media (max-width: 768px) {
    .reservation-steps {
        padding: 15px;
    }

    .time-picker {
        flex-direction: column;
        gap: 10px;
    }

    .time-picker select {
        width: 100%;
    }

    .availability-grid {
        grid-template-columns: 1fr !important;
        gap: 10px;
    }

    .availability-item {
        padding: 15px 10px;
        font-size: 0.9rem;
    }
}

/* DASHBOARD RESPONSIVE */
@media (max-width: 768px) {
    .dashboard-card {
        margin-bottom: 20px;
    }

    .message-item {
        padding: 15px;
        margin-bottom: 10px;
    }

    .reservation-table {
        font-size: 0.8rem;
    }
}

/* GALLERY RESPONSIVE */
@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
    }

    .gallery-item {
        padding: 10px;
    }

    .gallery-item img {
        border-radius: 8px;
    }
}

@media (max-width: 480px) {
    .gallery-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="images/logo.jpg" alt="MCC Logo"> MCC Seafood Restaurant
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-center">

        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        $is_menu_page = in_array($current_page, ['food-menu.php', 'drinks-menu.php']);
        $is_more_page = in_array($current_page, ['more.php', 'services.php', 'faq.php', 'terms.php']);
        ?>

        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="about.php">About</a></li>

        <!-- MENU -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?php echo $is_menu_page ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">Menu</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item <?php echo ($current_page == 'food-menu.php') ? 'active' : ''; ?>" href="food-menu.php">Food Menu</a></li>
            <li><a class="dropdown-item <?php echo ($current_page == 'drinks-menu.php') ? 'active' : ''; ?>" href="drinks-menu.php">Drinks Menu</a></li>
          </ul>
        </li>

        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'gallery.php') ? 'active' : ''; ?>" href="gallery.php">Gallery</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
        <?php if (isset($_SESSION['username'])): ?>
          <li class="nav-item"><a class="nav-link book-now-btn <?php echo ($current_page == 'reserve.php') ? 'active' : ''; ?>" href="reserve.php">Book Now</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link book-now-btn" data-bs-toggle="modal" data-bs-target="#loginModal">Reserve Table</a></li>
        <?php endif; ?>

        <!-- MORE -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?php echo $is_more_page ? 'active' : ''; ?>" href="#" data-bs-toggle="dropdown">More</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item <?php echo ($current_page == 'more.php') ? 'active' : ''; ?>" href="more.php">Overview</a></li>
            <li><a class="dropdown-item <?php echo ($current_page == 'services.php') ? 'active' : ''; ?>" href="services.php">Services</a></li>
            <li><a class="dropdown-item <?php echo ($current_page == 'faq.php') ? 'active' : ''; ?>" href="faq.php">FAQs</a></li>
            <li><a class="dropdown-item <?php echo ($current_page == 'terms.php') ? 'active' : ''; ?>" href="terms.php">Terms & Policies</a></li>
          </ul>
        </li>

        <?php
        // Determine which menu to show based on page context and available sessions
        $current_page = basename($_SERVER['PHP_SELF']);
        $admin_pages = ['admin-dashboard.php', 'admin-login.php'];
        $user_pages = ['dashboard.php', 'reserve.php'];

        $show_admin_menu = false;
        $show_user_menu = false;

        if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_username'])) {
            $show_admin_menu = true;
        }
        if (isset($_SESSION['username'])) {
            $show_user_menu = true;
        }

        // If both sessions exist, show appropriate menu based on page context
        if ($show_admin_menu && $show_user_menu) {
            if (in_array($current_page, $admin_pages)) {
                $show_user_menu = false;
            } elseif (in_array($current_page, $user_pages)) {
                $show_admin_menu = false;
            } else {
                // On public pages, prefer user menu
                $show_admin_menu = false;
            }
        }
        ?>

        <?php if ($show_admin_menu): ?>
          <!-- ADMIN LOGGED IN -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-shield-check me-2"></i> Admin: <?php echo $_SESSION['admin_username']; ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="admin-dashboard.php">Admin Dashboard</a></li>
              <li><a class="dropdown-item" href="admin-logout.php">Admin Logout</a></li>
            </ul>
          </li>
        <?php elseif ($show_user_menu): ?>
          <!-- USER LOGGED IN -->
          <?php
          // Get unread messages count for the user
          $unread_count = 0;
          if (isset($_SESSION['user_id'])) {
              $user_id = $_SESSION['user_id'];
              $sql = "SELECT COUNT(*) as unread_count FROM user_messages WHERE recipient_id = ? AND is_read = 0";
              $stmt = $conn->prepare($sql);
              if ($stmt) {
                  $stmt->bind_param("i", $user_id);
                  if ($stmt->execute()) {
                      $result = $stmt->get_result();
                      if ($result && $row = $result->fetch_assoc()) {
                          $unread_count = $row['unread_count'];
                      }
                  }
                  $stmt->close();
              }
          }
          ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-2"></i> <?php echo $_SESSION['username']; ?>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item d-flex align-items-center" href="dashboard.php">
                <i class="bi bi-house-door me-2"></i> Dashboard
              </a></li>
              <li><a class="dropdown-item d-flex align-items-center" href="dashboard.php#messages">
                <i class="bi <?php echo $unread_count > 0 ? 'bi-envelope-exclamation-fill text-warning' : 'bi-envelope'; ?> me-2"></i> Messages
                <?php if ($unread_count > 0): ?>
                  <span class="badge bg-danger ms-auto"><?php echo $unread_count; ?></span>
                <?php endif; ?>
              </a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item d-flex align-items-center" href="logout.php">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a></li>
            </ul>
          </li>
        <?php else: ?>
          <!-- NOT LOGGED IN -->
          <li class="nav-item">
            <button class="btn btn-reserve" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login / Register</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="authTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">User Login</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">Register</button>
          </li>
        </ul>
        <div class="tab-content" id="authTabContent">
          <div class="tab-pane fade show active" id="login" role="tabpanel">
            <div id="loginAlertContainer"></div>
            <form action="login.php" method="post">
              <div class="mb-3">
                <label for="loginUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="loginUsername" name="username" required>
              </div>
              <div class="mb-3">
                <label for="loginPassword" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="loginPassword" name="password" required>
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Login</button>
            </form>
          </div>
          <div class="tab-pane fade" id="register" role="tabpanel">
            <div id="registerAlertContainer"></div>
            <form action="register.php" method="post">
              <div class="mb-3">
                <label for="regUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="regUsername" name="username" required>
              </div>
              <div class="mb-3">
                <label for="regEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="regEmail" name="email" required>
              </div>
              <div class="mb-3">
                <label for="regPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="regPassword" name="password" required>
              </div>
              <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <hr>
            <p class="text-center text-muted small">Admin? <a href="admin-login.php" class="text-decoration-none">Login as Admin here</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const params = new URLSearchParams(window.location.search);
    const auth = params.get('auth');
    const reason = params.get('reason');

    function showModal(tabId, message, type){
        const loginModalEl = document.getElementById('loginModal');
        const modal = new bootstrap.Modal(loginModalEl);
        if (tabId === 'register') {
            var tabTrigger = document.querySelector('#register-tab');
            if (tabTrigger) tabTrigger.click();
        } else {
            var tabTrigger = document.querySelector('#login-tab');
            if (tabTrigger) tabTrigger.click();
        }

        // Insert alert
        const containerId = tabId === 'register' ? 'registerAlertContainer' : 'loginAlertContainer';
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '<div class="alert alert-' + type + ' mt-3">' + message + '</div>';
        }

        modal.show();

        // remove params from URL without reloading
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('auth');
            url.searchParams.delete('reason');
            window.history.replaceState({}, document.title, url.toString());
        }
    }

    if (auth === 'login_failed') {
        let msg = 'Login failed. Please check your credentials.';
        if (reason === 'not_found') msg = 'No account found with that username.';
        if (reason === 'invalid_password') msg = 'Invalid password.';
        showModal('login', msg, 'warning');
    }

    if (auth === 'register_failed') {
        let msg = 'Registration failed. Please try again.';
        if (reason === 'exists') msg = 'Username or email already registered.';
        if (reason === 'db_error') msg = 'A server error occurred. Please try again later.';
        showModal('register', msg, 'warning');
    }

    if (auth === 'register_success') {
        showModal('login', 'Registration successful. Please log in.', 'success');
    }
});

// Password toggle functionality
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('loginPassword');
    const icon = this.querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>