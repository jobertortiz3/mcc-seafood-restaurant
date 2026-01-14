<?php
session_start();
$page_title = 'Drinks Menu | MCC Seafood Restaurant';
include 'header.php';
?>

<style>
/* Dropdown Menu */
.dropdown-menu.custom {
    background: linear-gradient(180deg,#ffffff,#ffffff);
    border: none;
    min-width: 220px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.35);
}
.dropdown-menu.custom .dropdown-item {
    color: #000000;
    padding: 18px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: 1.05rem;
    font-weight: bold;
}
.dropdown-menu.custom .dropdown-item:hover {
    background: rgba(255,255,255,0.06);
    color: #fff;
}

/* Cards animation */
@keyframes fadeInUp { from {opacity:0; transform: translateY(30px);} to {opacity:1; transform: translateY(0);} }
@keyframes zoomIn { from {opacity:0; transform: scale(0.9);} to {opacity:1; transform: scale(1);} }

.card { animation: fadeInUp 1s ease forwards; display: flex; flex-direction: column; height: 100%; }
.card img { animation: zoomIn 1s ease forwards; height: 200px; object-fit: cover; border-radius: 5px 5px 0 0; }
.card-body { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }

/* Buttons */
.btn-order { background-color: #ffd700; color: #8b0000; font-weight: bold; }
.btn-order:hover { background-color: #ffcc00; color: #8b0000; }
.btn-back { margin-bottom: 20px; }

/* Responsive dropdown for small screens */
@media (max-width: 576px) {
    .dropdown-menu.custom { left: 0 !important; right: 0 !important; }
}
</style>

<!-- Drinks Cards -->
<div class="container mt-5">
  <a href="menu.php" class="btn btn-light btn-back">⬅ Back to Menu</a>

  <div class="row g-4">

<?php
include 'config.php';
$drinks_items = $conn->query("SELECT * FROM menu_items WHERE category = 'Drinks' ORDER BY name");

if ($drinks_items->num_rows > 0) {
    while ($item = $drinks_items->fetch_assoc()) {
        echo '<div class="col-12 col-sm-6 col-md-4">
<div class="card text-dark">';
        if ($item['image']) {
            echo '<img src="' . htmlspecialchars($item['image']) . '" class="card-img-top" alt="' . htmlspecialchars($item['name']) . '">';
        }
        echo '<div class="card-body d-flex flex-column">
<h5 class="card-title text-center">' . htmlspecialchars($item['name']) . '</h5>
<p class="card-text text-center">₱' . number_format($item['price'], 2) . '</p>';
        if ($item['description']) {
            echo '<p class="card-text small text-muted text-center">' . htmlspecialchars($item['description']) . '</p>';
        }
        echo '</div>
</div>
</div>';
    }
} else {
    echo '<div class="col-12 text-center">
        <p class="text-muted">Drink items will be added soon. Check back later!</p>
    </div>';
}
?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
