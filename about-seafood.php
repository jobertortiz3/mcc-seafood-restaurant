<?php
session_start();
$page_title = 'Our Seafood | MCC Seafood Restaurant';
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
            <li class="nav-item">
                <a class="nav-link" href="about.php">Who We Are</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="about-seafood.php">Our Seafood</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="about-place.php">Our Place</a>
            </li>
        </ul>
    </div>

    <!-- CONTENT -->
    <div class="col-md-9 p-5">
        <h2>Our Seafood</h2>
        <p>
            At MCC Seafood Restaurant, freshness is our promise. We work closely
            with local fishermen to bring in the finest catch every day.
        </p>
        <p>
            From shrimp and crabs to fresh fish and shellfish, every dish is
            carefully prepared to preserve its natural flavor.
        </p>

        <img src="images/12.jpg" class="img-fluid" alt="Fresh Seafood">
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
