<?php
session_start();
$page_title = 'Menu | MCC Seafood Restaurant';
include 'header.php';
?>

<style>
/* SIDEBAR */
.sidebar {
    background-color: #8b0000;
    min-height: 100vh;
}
.sidebar h4 {
    border-bottom: 1px solid rgba(255,255,255,0.3);
    padding-bottom: 10px;
}
.sidebar .nav-link {
    color: #ffffff;
    padding: 10px;
    border-radius: 6px;
    font-weight: bold;
}
.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background-color: rgba(255,255,255,0.2);
}

/* CONTENT */
.content img {
    border-radius: 15px;
    margin-top: 15px;
}

/* Cards */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}
.card:hover {
    transform: translateY(-5px);
}
.card-img-top {
    border-radius: 15px 15px 0 0;
    height: 200px;
    object-fit: cover;
}
.btn-order {
    background-color: #ffd700;
    color: #8b0000;
    font-weight: bold;
    border: none;
}
.btn-order:hover {
    background-color: #ffcc00;
    color: #8b0000;
}
</style>

<!-- Add this section below the navbar, before the Bootstrap JS script -->
<div class="container mt-4">
  <div class="row align-items-center">
    <div class="col-12">
      <div class="position-relative text-center">
        <img src="images/m18.jpg" class="img-fluid rounded" style="height: 300px; object-fit: cover; width: 100%;" alt="Menu Overview">
        <div class="position-absolute top-50 start-50 translate-middle text-white" style="background: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 8px;">
          <h2>Explore Our Delicious Dishes</h2>
          <p>From fresh seafood platters to savory grilled specialties, our menu offers a wide variety of dishes crafted to delight every taste. Explore the flavors of the sea, made with passion and the finest ingredients</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Menu Categories -->
<div class="container mt-5">
  <div class="row text-center">
    <div class="col-md-6 mb-4">
      <div class="card h-100">
        <img src="images/m1.jpg" class="card-img-top" alt="Food Menu">
        <div class="card-body">
          <h5 class="card-title">Food Menu</h5>
          <p class="card-text">Discover our exquisite selection of fresh seafood dishes, from grilled specialties to traditional favorites.</p>
          <a href="food-menu.php" class="btn btn-order">View Food Menu</a>
        </div>
      </div>
    </div>
    <div class="col-md-6 mb-4">
      <div class="card h-100">
        <img src="images/d1.jpg" class="card-img-top" alt="Drinks Menu">
        <div class="card-body">
          <h5 class="card-title">Drinks Menu</h5>
          <p class="card-text">Quench your thirst with our refreshing beverages, including cocktails, wines, and non-alcoholic options.</p>
          <a href="drinks-menu.php" class="btn btn-order">View Drinks Menu</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (bundle includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>