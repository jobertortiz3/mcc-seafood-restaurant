<?php
session_start();
$page_title = 'Order | MCC Seafood Restaurant';
include 'header.php';
?>

<style>
.form-control, .btn { border-radius: 0; }
.btn-submit { background-color: #ffd700; color: #8b0000; font-weight: bold; }
.btn-submit:hover { background-color: #ffcc00; color: #8b0000; }
</style>

<div class="container mt-5">
  <a href="menu.php" class="btn btn-light mb-3">⬅ Back to Menu</a>

  <h2 class="mb-4">Order Details</h2>

  <form id="orderForm">
    <div class="mb-3">
      <label class="form-label">Item Name</label>
      <input type="text" class="form-control" id="item_name" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Price</label>
      <input type="text" class="form-control" id="item_price" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Quantity</label>
      <input type="number" class="form-control" id="quantity" value="1" min="1" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Customer Name</label>
      <input type="text" class="form-control" id="customer_name" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Contact Number</label>
      <input type="tel" class="form-control" id="customer_contact" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Order Type</label>
      <select class="form-select" id="order_type" required>
        <option value="">Select...</option>
        <option value="Dine-In">Dine-In</option>
        <option value="Takeout">Takeout</option>
      </select>
    </div>

    <button type="submit" class="btn btn-submit w-100">Place Order</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Get query params from URL
const params = new URLSearchParams(window.location.search);
document.getElementById('item_name').value = params.get('item') || '';
document.getElementById('item_price').value = params.get('price') || '';

// Handle form submission
document.getElementById('orderForm').addEventListener('submit', function(e){
  e.preventDefault();
  alert(`Order Placed!\n\nItem: ${document.getElementById('item_name').value}\nPrice: ${document.getElementById('item_price').value}\nQuantity: ${document.getElementById('quantity').value}\nCustomer: ${document.getElementById('customer_name').value}\nContact: ${document.getElementById('customer_contact').value}\nOrder Type: ${document.getElementById('order_type').value}`);
  this.reset();
});
</script>
</body>
</html>
