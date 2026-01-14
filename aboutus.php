<?php
session_start();
$page_title = 'About Us | MCC Seafood Restaurant';
include 'header.php';
?>

<style>
/* Dropdown menu styles */
.dropdown-menu.custom {
    background-color: #8b0000;
    border: none;
}

.dropdown-menu.custom .dropdown-item {
    color: #fff;
    font-weight: bold;
}

.dropdown-menu.custom .dropdown-item:hover {
    background-color: rgba(255,255,255,0.15);
}

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

.sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.15);
}

/* CONTENT */
.content img {
    border-radius: 15px;
    margin-top: 15px;
}
</style>
<!-- PAGE CONTENT -->
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
                    <a class="nav-link" href="about-seafood.php">Our Seafood</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about-place.php">Our Place</a>
                </li>
            </ul>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9 content p-5">
            <h2>Who We Are</h2>
            <p>
                MCC Seafood Restaurant serves fresh seafood sourced daily from local
                fishermen and prepared by skilled chefs who value quality and taste.
            </p>

            <img src="17.jpg" class="img-fluid" alt="MCC Seafood Restaurant">
        </div>

    </div>
</div>

</body>
</html>
