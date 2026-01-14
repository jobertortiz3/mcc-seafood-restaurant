<?php
session_start();
$page_title = 'Gallery | MCC Seafood Restaurant';
include 'header.php';
include 'config.php';
?>

<style>
/* Dropdown Menu */
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
            font-weight: bold; /* FIX */
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.15);
}

/* Section Titles */
.section-title {
  text-align: center;
  margin: 40px 0 20px;
  font-size: 2rem;
  font-weight: bold;
}
.section-description {
  text-align: center;
  margin-bottom: 40px;
  font-size: 1.1rem;
  color: #ffd700;
}

/* Gallery */
.gallery img, .crew img {
  width: 100%;
  height: 250px;
  object-fit: cover;
  border-radius: 10px;
  animation: fadeInUp 1s ease forwards;
}

/* Crew Cards */
.crew-card {
  background-color: #8b0000;
  border-radius: 10px;
  text-align: center;
  padding: 15px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.crew-card h5 { margin-top: 10px; font-weight: bold; color: #ffd700; }
.crew-card p { color: #fff; margin-bottom: 0; }

/* Animations */
@keyframes fadeInUp {
  from { opacity:0; transform: translateY(30px); }
  to { opacity:1; transform: translateY(0); }
}

/* Footer */
footer {
  background-color: #8b0000;
  padding: 25px 0;
  margin-top: 40px;
  text-align: center;
  color: #fff;
}

/* Reserve Button */
.btn-reserve {
  background-color: #ffd700;
  color: #8b0000;
  font-weight: bold;
  font-size: 1.2rem;
  padding: 12px 30px;
  border-radius: 8px;
  transition: 0.3s;
}
.btn-reserve:hover {
  background-color: #ffcc00;
  color: #8b0000;
}
</style>

<!-- Customer Photos Section -->
<div class="container">
  <h2 class="section-title">Our Facilities</h2>
  <p class="section-description">
    Explore our modern facilities and dining areas at MCC Seafood.
  </p>

  <?php
  $facilities_images = $conn->query("SELECT * FROM gallery WHERE category = 'facilities' ORDER BY uploaded_at DESC");
  if ($facilities_images->num_rows > 0): ?>
    <div class="row g-4 gallery">
      <?php while ($image = $facilities_images->fetch_assoc()): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
               alt="<?php echo htmlspecialchars($image['image_name']); ?>" 
               title="Uploaded on <?php echo date('M d, Y h:i A', strtotime($image['uploaded_at'])); ?>">
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <p class="text-muted">Facility photos will be uploaded soon. Check back later!</p>
    </div>
  <?php endif; ?>

  <!-- Restaurant Gallery Section -->
  <h2 class="section-title">Our Happy Customers</h2>
  <p class="section-description">
    See our customers enjoying fresh seafood and a memorable dining experience at MCC Seafood.
  </p>

  <?php
  $customers_images = $conn->query("SELECT * FROM gallery WHERE category = 'customers' ORDER BY uploaded_at DESC");
  if ($customers_images->num_rows > 0): ?>
    <div class="row g-4 gallery">
      <?php while ($image = $customers_images->fetch_assoc()): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
               alt="<?php echo htmlspecialchars($image['image_name']); ?>" 
               title="Uploaded on <?php echo date('M d, Y h:i A', strtotime($image['uploaded_at'])); ?>">
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <p class="text-muted">Customer photos will be uploaded soon. Check back later!</p>
    </div>
  <?php endif; ?>

  <!-- Reserve Button -->
  <div class="text-center mt-4 mb-5">
    <?php if (isset($_SESSION['username'])): ?>
      <a href="reserve.php" class="btn btn-reserve">
        🍽️ BOOK NOW!
      </a>
    <?php else: ?>
      <button class="btn btn-reserve" data-bs-toggle="modal" data-bs-target="#loginModal">
        🍽️ BOOK NOW!
      </button>
    <?php endif; ?>
  </div>

  <!-- Crew Section -->
  <h2 class="section-title">Meet Our MCC Family</h2>
  <p class="section-description">
    Our talented team makes every dining experience at MCC Seafood special.
  </p>

  <?php
  $family_images = $conn->query("SELECT * FROM gallery WHERE category = 'MCC family' ORDER BY uploaded_at DESC");
  if ($family_images->num_rows > 0): ?>
    <div class="row g-4 gallery">
      <?php while ($image = $family_images->fetch_assoc()): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
               alt="<?php echo htmlspecialchars($image['image_name']); ?>" 
               title="Uploaded on <?php echo date('M d, Y h:i A', strtotime($image['uploaded_at'])); ?>">
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-5">
      <p class="text-muted">Team photos will be uploaded soon. Check back later!</p>
    </div>
  <?php endif; ?>
</div>

<!-- Footer -->
<footer>
  <p>&copy; 2025 MCC Seafood Restaurant | 📍 Hda. Bobby, Slay City, Philippines | ☎️ 0912-345-6789</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
