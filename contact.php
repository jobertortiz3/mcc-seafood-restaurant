<?php
session_start();
$page_title = 'Contact Us | MCC Seafood Restaurant';
include 'header.php';
include 'config.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $user_message = trim($_POST['message'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($user_message)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $subject, $user_message);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Thank you for your message! We will get back to you soon.</div>';
            } else {
                $message = '<div class="alert alert-danger">Sorry, there was an error sending your message. Please try again.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Please enter a valid email address.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    }
}
?>

<style>
/* Page Title */
.page-title {
  text-align: center;
  margin: 40px 0 20px;
  font-size: 2rem;
  font-weight: bold;
}

/* Form Styles */
.contact-form {
  background-color: #8b0000;
  padding: 30px;
  border-radius: 10px;
}
.contact-form .form-control {
  background-color: #ffffff;
  color: #000;
  border: 1px solid #ffd700;
}
.contact-form .form-control:focus {
  background-color: #ffffff;
  color: #000;
  border-color: #ffcc00;
  box-shadow: none;
}
.contact-form label { color: #ffd700; }
.contact-form button {
  background-color: #ffd700;
  color: #8b0000;
  font-weight: bold;
}
.contact-form button:hover { background-color: #ffcc00; color: #8b0000; }

/* Contact Info */
.contact-info {
  margin-top: 30px;
}
.contact-info h5 { color: #ffd700; }
.contact-info p { color: #fff; }
</style>

<!-- Page Title -->
<div class="container">
  <h2 class="page-title">Contact Us</h2>
  <p class="text-center" style="color:#ffd700;">We would love to hear from you! Send us a message or find our contact info below.</p>

  <div class="row mt-4">
    <!-- Contact Form -->
    <div class="col-lg-6 mb-4">
      <?php echo $message; ?>
      <div class="contact-form">
        <form method="POST" action="">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Your Name" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Your Email" required>
          </div>
          <div class="mb-3">
            <label for="phone" class="form-label">Phone (Optional)</label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="Your Phone Number">
          </div>
          <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control" placeholder="Subject" required>
          </div>
          <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea id="message" name="message" rows="5" class="form-control" placeholder="Write your message..." required></textarea>
          </div>
          <button type="submit" class="btn btn-order w-100">Send Message</button>
        </form>
      </div>
    </div>

    <!-- Contact Info -->
    <div class="col-lg-6 mb-4">
      <div class="contact-info">
        <h5>Visit Us</h5>
        <p>Hda. Bobby, Slay City, Philippines</p>

        <h5>Call Us</h5>
        <p>+63 912-345-6789</p>

        <h5>Email</h5>
        <p>info@mccseafood.com</p>

        <h5>Opening Hours</h5>
        <p>Monday - Sunday: 10:00 AM - 10:00 PM</p>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="mt-5">
  <p style="text-align:center; padding:25px; background-color:#8b0000; margin:0;">&copy; 2025 MCC Seafood Restaurant</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('message').value.trim();
        
        if (!name || !email || !subject || !message) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Sending...';
    });
});
</script>
</body>
</html>
