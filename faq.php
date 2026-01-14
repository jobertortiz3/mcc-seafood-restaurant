<?php
session_start();
$page_title = 'FAQs | MCC Seafood Restaurant';
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
<h2 class="mb-4">Frequently Asked Questions</h2>

<div class="accordion" id="faq">

<div class="accordion-item card">
<h2 class="accordion-header">
<button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#q1">
Do you accept reservations?
</button>
</h2>
<div id="q1" class="accordion-collapse collapse show">
<div class="accordion-body">
Yes, you can reserve a table online or by contacting us.
</div>
</div>
</div>

<div class="accordion-item card">
<h2 class="accordion-header">
<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q2">
Do you offer private events?
</button>
</h2>
<div id="q2" class="accordion-collapse collapse">
<div class="accordion-body">
Yes, we accommodate birthdays, anniversaries, and group events.
</div>
</div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
