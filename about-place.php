<?php
session_start();
$page_title = 'Our Place | MCC Seafood Restaurant';
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
</style>

<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <div class="col-md-3 sidebar p-4">
        <h4>About Us</h4>
        <ul class="nav flex-column mt-3">
            <li class="nav-item"><a class="nav-link" href="about.php">Who We Are</a></li>
            <li class="nav-item"><a class="nav-link" href="about-seafood.php">Our Seafood</a></li>
            <li class="nav-item"><a class="nav-link active" href="about-place.php">Our Place</a></li>
        </ul>
    </div>

    <!-- CONTENT -->
    <div class="col-md-9 p-5 content">
        <h2>Our Place</h2>
        <p>
            MCC Seafood Restaurant is located near the coast, offering a relaxing
            atmosphere where guests can enjoy delicious meals with family and friends.
        </p>
        <p>
            Our place is designed to be comfortable, clean, and welcoming — perfect
            for celebrations, casual dining, and special occasions.
        </p>
        <img src="images/19.jpg" class="img-fluid">
    </div>

</div>
</div>

<!-- ✅ REQUIRED FOR DROPDOWNS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
