<?php
session_start();
$page_title = 'Overview | MCC Seafood Restaurant';
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

<div class="container mt-5">
<div class="content-box">
<h2>Restaurant Overview</h2>
<p>
MCC Seafood Restaurant is dedicated to serving freshly prepared seafood dishes
in a warm and family-friendly environment. From casual dining to special events,
we provide an unforgettable coastal dining experience.
</p>
</div>
</div>

<footer></footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
