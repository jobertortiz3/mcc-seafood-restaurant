<?php
session_start();
$page_title = 'Terms & Policies | MCC Seafood Restaurant';
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

<div class="container mt-5 section">
<h2>Terms & Policies</h2>

<p><strong>Reservations:</strong> Subject to availability.</p>
<p><strong>Cancellation:</strong> Please notify us at least 24 hours before.</p>
<p><strong>Payments:</strong> Cash and digital payments accepted.</p>
<p><strong>Behavior:</strong> Guests are expected to respect staff and property.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
