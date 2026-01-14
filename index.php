<?php
session_start();
$page_title = 'MCC Seafood Restaurant | Home';
include 'header.php';
?>

<!-- MAIN CONTENT -->
<div class="container mt-5 text-center px-3">
    <?php if (isset($_GET['message']) && $_GET['message'] == 'account_deleted'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Account Deleted Successfully!</strong> Your account and all associated data have been permanently removed.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h1 class="mb-3">Welcome to MCC Seafood Restaurant</h1>
    <p class="lead mb-4">Fresh from the sea, cooked with passion.</p>

    <?php 
    $has_user_session = isset($_SESSION['username']) && isset($_SESSION['user_id']);
    $has_admin_session = isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']);
    
    if ($has_user_session && !$has_admin_session): ?>
      <a href="reserve.php" class="btn btn-reserve btn-lg mt-3">
        🍽️ Book Now
      </a>
    <?php elseif ($has_admin_session && !$has_user_session): ?>
      <a href="admin-dashboard.php" class="btn btn-admin btn-lg mt-3">
        🔧 Admin Dashboard
      </a>
    <?php elseif ($has_user_session && $has_admin_session): ?>
      <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center align-items-center">
        <a href="reserve.php" class="btn btn-reserve btn-lg">
          🍽️ Book Now
        </a>
       
      </div>
    <?php else: ?>
      <button class="btn btn-reserve btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#loginModal">
        🍽️ Reserve Your Table Today
      </button>
    <?php endif; ?>

    <!-- CAROUSEL -->
    <div id="seafoodCarousel" class="carousel slide mt-5" data-bs-ride="carousel">
      <div class="carousel-inner rounded">
        <div class="carousel-item active">
          <img src="images/12.jpg" class="d-block w-100" alt="Fresh Seafood Dish" loading="lazy">
        </div>
        <div class="carousel-item">
          <img src="images/13.jpg" class="d-block w-100" alt="Restaurant Interior" loading="lazy">
        </div>
        <div class="carousel-item">
          <img src="images/11.jpg" class="d-block w-100" alt="Delicious Seafood Platter" loading="lazy">
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#seafoodCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#seafoodCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
</div>

<!-- FEATURE -->
<div class="container feature-image mt-5 text-center px-3">
    <h2 class="mb-3">Experience the Taste of the Sea</h2>
    <p class="lead mb-4">A perfect place for family gatherings and celebrations.</p>
    <img src="images/14.jpg" class="img-fluid rounded shadow" alt="Restaurant Experience">
</div>

<!-- FOOTER -->
<footer class="text-center py-4 mt-5">
  <div class="container px-3">
    <p class="mb-2">&copy; 2025 MCC Seafood Restaurant</p>
    <p class="mb-0 small">
      <i class="bi bi-geo-alt-fill me-1"></i>Hda. Bobby, Slay City, Philippines | 
      <i class="bi bi-telephone-fill ms-2 me-1"></i>0912-345-6789
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>