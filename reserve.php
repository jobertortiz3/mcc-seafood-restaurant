<?php
session_start();
// Check for user session - allow concurrent admin and user sessions
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $reservation_date = $_POST['reservation_date'];
    $reservation_time = $_POST['reservation_time'];
    $guests = $_POST['guests'];
    $reservation_type = $_POST['reservation_type'];
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $special_requests = $_POST['special_requests'];
    // Basic server-side validation
    if (empty($reservation_type) || empty($item_id) || $item_id <= 0) {
        $error_message = "Please select a valid table or cottage before submitting.";
    } else {
        // Normalize reservation_time to 5-minute increments (server-side safeguard)
        if (!empty($reservation_time)) {
            $parts = explode(':', $reservation_time);
            if (count($parts) >= 2) {
                $hour = intval($parts[0]);
                $min = intval($parts[1]);
                $roundedMin = (int) round($min / 5) * 5;
                if ($roundedMin == 60) { $hour = ($hour + 1) % 24; $roundedMin = 0; }
                // Ensure TIME format HH:MM:SS for DB
                $reservation_time = sprintf('%02d:%02d:00', $hour, $roundedMin);
            }
        }
        // Check availability again before booking to prevent race conditions
        $availability_check = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_date = ? AND reservation_time = ? AND reservation_type = ? AND item_id = ? AND status = 'confirmed'");
        $availability_check->bind_param("sssi", $reservation_date, $reservation_time, $reservation_type, $item_id);
        $availability_check->execute();
        $availability_result = $availability_check->get_result();
        $availability_row = $availability_result->fetch_assoc();
        
        if ($availability_row['count'] > 0) {
            $error_message = "Sorry, this table/cottage is no longer available for the selected date and time. Please choose a different option.";
        } else {
            $sql = "INSERT INTO reservations (user_id, full_name, email, phone, reservation_date, reservation_time, guests, reservation_type, item_id, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed')";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // types: i (user_id), s, s, s, s, s, i (guests), s (reservation_type), i (item_id), s (special_requests)
                $stmt->bind_param("isssssisis", $user_id, $full_name, $email, $phone, $reservation_date, $reservation_time, $guests, $reservation_type, $item_id, $special_requests);

                if ($stmt->execute()) {
                    // redirect to avoid form resubmission and show success message
                    header("Location: reserve.php?success=1");
                    exit;
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Database error: could not prepare statement.";
            }
        }
        $availability_check->close();
    }
}

$page_title = 'Reserve Table | MCC Seafood Restaurant';
include 'header.php';
?>

<style>
/* FORM CONTAINER */
.form-container {
    max-width: 520px;
    margin: 50px auto;
    background-color: #8b0000;
    padding: 30px;
    border-radius: 15px;
}

/* FORM INPUTS */
.form-control,
textarea {
    background-color: #ffffff;
    color: #000000;
    border: 2px solid #ffd700;
}

.form-control::placeholder,
textarea::placeholder {
    color: #666;
}

.form-control:focus,
textarea:focus {
    background-color: #ffffff;
    color: #000000;
    border-color: #ffd700;
    box-shadow: none;
}

/* BUTTON */
.btn-reserve {
    background-color: #ffd700;
    color: #8b0000;
    font-weight: bold;
}
.btn-reserve:hover {
    background-color: #ffcc00;
    color: #8b0000;
}

/* TIME ARROWS */
.time-arrow {
    padding: 2px 5px;
    font-size: 0.8rem;
    line-height: 1;
}

/* TIME PICKER */
.time-picker {
    border: 2px solid #ffd700;
    border-radius: 5px;
    background-color: #ffffff;
    padding: 5px;
    display: inline-flex;
    align-items: center;
}
.time-part {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 2px;
}
.time-input {
    width: 30px;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: bold;
    font-size: 1rem;
}
.time-separator {
    font-weight: bold;
    margin: 0 5px;
}
.time-arrow {
    background: none;
    border: none;
    color: #8b0000;
    cursor: pointer;
    font-size: 0.7rem;
    padding: 0;
    width: 20px;
    height: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.time-arrow:hover {
    background-color: #f0f0f0;
}

.time-section .btn-outline-primary {
    border-color: #ffffff;
    color: #ffffff;
    background-color: transparent;
    transition: all 0.3s ease;
}

.time-section .btn-outline-primary:hover {
    background-color: #ffffff;
    border-color: #ffffff;
    color: #000000;
}

/* RESPONSIVE DESIGN FOR RESERVATION SYSTEM */
@media (max-width: 768px) {
    .form-container {
        padding: 20px 15px;
        margin: 20px 10px;
    }

    .step {
        padding: 20px 15px;
    }

    .time-picker {
        flex-direction: column;
        gap: 15px;
    }

    .time-picker select,
    .time-picker input {
        width: 100%;
        font-size: 16px; /* Prevents zoom on iOS */
    }

    .availability-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .availability-item {
        padding: 15px 10px;
        font-size: 0.9rem;
        min-height: auto;
    }

    .availability-item.selected {
        transform: scale(1.02);
    }

    .btn-reserve {
        padding: 12px 20px;
        font-size: 1rem;
    }

    .d-grid {
        display: block !important;
    }

    .d-grid .btn {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }

    .d-grid .btn:last-child {
        margin-bottom: 0;
    }

    .form-control {
        font-size: 16px; /* Prevents zoom on iOS */
        padding: 12px 16px;
    }

    .modal-dialog {
        margin: 10px;
        max-width: calc(100vw - 20px);
    }

    .reservation-summary {
        font-size: 0.9rem;
    }

    .reservation-summary .row > div {
        margin-bottom: 10px;
    }
}

@media (max-width: 480px) {
    .form-container {
        padding: 15px 10px;
        margin: 15px 5px;
    }

    .step h4 {
        font-size: 1.3rem;
    }

    .availability-item {
        padding: 12px 8px;
        font-size: 0.85rem;
    }

    .btn-reserve {
        padding: 10px 16px;
        font-size: 0.95rem;
    }

    .time-section {
        padding: 15px;
    }

    .time-picker {
        gap: 10px;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .availability-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .form-container {
        max-width: 800px;
        margin: 30px auto;
    }
}
</style>

<!-- RESERVATION FORM -->
<div class="form-container">
    <h3 class="text-center mb-4">Make a Reservation</h3>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        <div class="alert alert-success">Reservation confirmed successfully! Your table/cottage is now booked.</div>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Step 1: Choose Reservation Type -->
    <div id="step1" class="step">
        <h4 class="text-center mb-3">What would you like to reserve?</h4>
        <div class="d-grid gap-3">
            <button type="button" class="btn btn-reserve btn-lg" onclick="selectType('table')">Reserve a Table</button>
            <button type="button" class="btn btn-reserve btn-lg" onclick="selectType('cottage')">Reserve a Cottage</button>
        </div>
    </div>

    <!-- Step 2: Select Date/Time and Check Availability -->
    <div id="step2" class="step" style="display: none;">
        <h4 class="text-center mb-3">Choose Your <span id="typeTitle"></span></h4>
        
        <div class="mb-3">
            <label for="reservation_date">Reservation Date:</label>
            <input type="date" id="reservation_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        
        <div class="mb-3 time-section">
            <label for="reservation_time">Reservation Time: <i class="bi bi-clock"></i> (EST)</label>
            <div class="d-flex align-items-center mb-2">
                <div class="time-picker d-flex align-items-center">
                    <div class="time-part">
                        <button type="button" class="time-arrow up" id="hourUp">▲</button>
                        <input type="text" id="hourInput" class="time-input" value="8" maxlength="2">
                        <button type="button" class="time-arrow down" id="hourDown">▼</button>
                    </div>
                    <span class="time-separator">:</span>
                    <div class="time-part">
                        <button type="button" class="time-arrow up" id="minuteUp">▲</button>
                        <input type="text" id="minuteInput" class="time-input" value="00" maxlength="2">
                        <button type="button" class="time-arrow down" id="minuteDown">▼</button>
                    </div>
                    <div class="time-part">
                        <button type="button" class="time-arrow up" id="ampmUp">▲</button>
                        <input type="text" id="ampmInput" class="time-input" value="AM" maxlength="2" readonly>
                        <button type="button" class="time-arrow down" id="ampmDown">▼</button>
                    </div>
                </div>
            </div>
            <div class="mb-2">
                <small>Quick select:</small><br>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setTime(11, 30, 'AM')">Early Lunch (11:30 AM)</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setTime(12, 30, 'PM')">Peak Lunch (12:30 PM)</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setTime(13, 30, 'PM')">Late Lunch (1:30 PM)</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setTime(17, 0, 'PM')">Early Dinner (5:00 PM)</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setTime(19, 30, 'PM')">Peak Dinner (7:30 PM)</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="setTime(19, 0, 'PM')">Late Dinner (7:00 PM)</button>
                </div>
            </div>
            <input type="hidden" id="reservation_time" required>
        </div>
        
        <div class="text-center mb-3">
            <button type="button" class="btn btn-reserve" onclick="checkAvailability()">Check Availability</button>
        </div>
        
        <div id="availabilityResults" style="display: none;">
            <h5 class="mt-3">Available Options:</h5>
            <div id="itemSelection" class="mb-3">
                <!-- Items will be loaded here -->
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="goBack()">Back</button>
                <button type="button" class="btn btn-reserve" id="continueBtn" onclick="continueToDetails()" disabled>Continue</button>
            </div>
        </div>
    </div>

    <!-- Step 3: Reservation Details -->
    <div id="step3" class="step" style="display: none;">
        <h4 class="text-center mb-3">Reservation Details</h4>
        <form method="post" action="" id="reservationForm">
            <input type="hidden" name="reservation_type" id="reservation_type">
            <input type="hidden" name="item_id" id="item_id">
            <input type="hidden" name="reservation_date" id="reservation_date_form">
            <input type="hidden" name="reservation_time" id="reservation_time_form">
            
            <input type="text" class="form-control mb-3" name="full_name" placeholder="Full Name" required>
            <input type="email" class="form-control mb-3" name="email" placeholder="Email" required>
            <input type="tel" class="form-control mb-3" name="phone" placeholder="Phone Number" required>

            <input type="number" class="form-control mb-3" name="guests" placeholder="Number of Guests" min="1" max="20" required>
            <textarea class="form-control mb-3" name="special_requests" rows="2" placeholder="Special requests (optional)"></textarea>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="goBackToSelection()">Back</button>
                <button type="submit" class="btn btn-reserve">Reserve Now</button>
            </div>
        </form>
    </div>
</div>

<script>
let selectedType = '';
let selectedItemId = '';

// Time picker functionality
document.addEventListener('DOMContentLoaded', function() {
    const hourInput = document.getElementById('hourInput');
    const minuteInput = document.getElementById('minuteInput');
    const ampmInput = document.getElementById('ampmInput');
    const timeInput = document.getElementById('reservation_time');

    function updateTime() {
        let hour = parseInt(hourInput.value) || 8;
        const minute = minuteInput.value.padStart(2, '0');
        const ampm = ampmInput.value.toUpperCase();
        if (ampm === 'PM' && hour !== 12) hour += 12;
        if (ampm === 'AM' && hour === 12) hour = 0;
        timeInput.value = String(hour).padStart(2, '0') + ':' + minute + ':00';
    }

    function validateHour() {
        let h = parseInt(hourInput.value);
        const ampm = ampmInput.value.toUpperCase();
        if (ampm === 'AM') {
            if (isNaN(h) || h < 8) h = 8;
            if (h > 12) h = 12;
        } else { // PM
            if (isNaN(h) || h < 1) h = 1;
            if (h > 8) h = 8;
        }
        hourInput.value = h;
        updateTime();
    }

    function validateMinute() {
        let m = parseInt(minuteInput.value);
        if (isNaN(m)) m = 0;
        // Round to nearest 15 minutes
        m = Math.round(m / 15) * 15;
        if (m >= 60) m = 45;
        if (m < 0) m = 0;
        minuteInput.value = String(m).padStart(2, '0');
        updateTime();
    }

    function validateAmpm() {
        let a = ampmInput.value.toUpperCase();
        if (a !== 'AM' && a !== 'PM') a = 'AM';
        ampmInput.value = a;
        validateHour(); // Re-validate hour when AM/PM changes
        updateTime();
    }

    // Manual input validation
    hourInput.addEventListener('input', validateHour);
    hourInput.addEventListener('blur', validateHour);
    minuteInput.addEventListener('input', validateMinute);
    minuteInput.addEventListener('blur', validateMinute);
    ampmInput.addEventListener('input', validateAmpm);
    ampmInput.addEventListener('blur', validateAmpm);

    // Mouse wheel support
    hourInput.addEventListener('wheel', function(e) {
        e.preventDefault();
        const ampm = ampmInput.value.toUpperCase();
        let h = parseInt(hourInput.value);
        if (e.deltaY < 0) {
            // Scroll up, increase
            if (ampm === 'AM') {
                h = h === 12 ? 8 : h + 1;
            } else {
                h = h === 8 ? 1 : h + 1;
            }
        } else {
            // Scroll down, decrease
            if (ampm === 'AM') {
                h = h === 8 ? 12 : h - 1;
            } else {
                h = h === 1 ? 8 : h - 1;
            }
        }
        hourInput.value = h;
        updateTime();
    });

    minuteInput.addEventListener('wheel', function(e) {
        e.preventDefault();
        let m = parseInt(minuteInput.value);
        if (e.deltaY < 0) {
            // Scroll up, increase
            m = (m + 15) % 60;
        } else {
            // Scroll down, decrease
            m = m === 0 ? 45 : m - 15;
        }
        minuteInput.value = String(m).padStart(2, '0');
        updateTime();
    });

    ampmInput.addEventListener('wheel', function(e) {
        e.preventDefault();
        ampmInput.value = ampmInput.value === 'AM' ? 'PM' : 'AM';
        validateAmpm();
    });

    // Hour arrows
    document.getElementById('hourUp').addEventListener('click', function() {
        let h = parseInt(hourInput.value);
        const ampm = ampmInput.value.toUpperCase();
        if (ampm === 'AM') {
            h = h === 12 ? 8 : h + 1;
        } else {
            h = h === 8 ? 1 : h + 1;
        }
        hourInput.value = h;
        updateTime();
    });
    document.getElementById('hourDown').addEventListener('click', function() {
        let h = parseInt(hourInput.value);
        const ampm = ampmInput.value.toUpperCase();
        if (ampm === 'AM') {
            h = h === 8 ? 12 : h - 1;
        } else {
            h = h === 1 ? 8 : h - 1;
        }
        hourInput.value = h;
        updateTime();
    });

    // Minute arrows (15-minute steps)
    document.getElementById('minuteUp').addEventListener('click', function() {
        let m = parseInt(minuteInput.value);
        m = (m + 15) % 60;
        minuteInput.value = String(m).padStart(2, '0');
        updateTime();
    });
    document.getElementById('minuteDown').addEventListener('click', function() {
        let m = parseInt(minuteInput.value);
        m = m === 0 ? 45 : m - 15;
        minuteInput.value = String(m).padStart(2, '0');
        updateTime();
    });

    // AM/PM arrows
    document.getElementById('ampmUp').addEventListener('click', function() {
        ampmInput.value = ampmInput.value === 'AM' ? 'PM' : 'AM';
        validateAmpm();
    });
    document.getElementById('ampmDown').addEventListener('click', function() {
        ampmInput.value = ampmInput.value === 'AM' ? 'PM' : 'AM';
        validateAmpm();
    });

    updateTime(); // Initial

    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('reservation_date').value = today;
});

function setTime(hour, minute, ampm) {
    document.getElementById('hourInput').value = hour;
    document.getElementById('minuteInput').value = String(minute).padStart(2, '0');
    document.getElementById('ampmInput').value = ampm;
    // Update hidden field
    let h = hour;
    if (ampm === 'PM' && hour !== 12) h += 12;
    if (ampm === 'AM' && hour === 12) h = 0;
    document.getElementById('reservation_time').value = String(h).padStart(2, '0') + ':' + String(minute).padStart(2, '0') + ':00';
}

function getNextAvailableTime(currentTime) {
    // Simple: add 30 minutes
    const [hours, minutes] = currentTime.split(':').map(Number);
    let newMinutes = minutes + 30;
    let newHours = hours;
    if (newMinutes >= 60) {
        newMinutes -= 60;
        newHours += 1;
    }
    return `${String(newHours).padStart(2, '0')}:${String(newMinutes).padStart(2, '0')}`;
}

function selectType(type) {
    selectedType = type;
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    document.getElementById('typeTitle').textContent = type.charAt(0).toUpperCase() + type.slice(1);
}

function checkAvailability() {
    const date = document.getElementById('reservation_date').value;
    const time = document.getElementById('reservation_time').value;
    
    console.log('Checking availability with:', {selectedType, date, time});
    
    if (!selectedType) {
        alert('Please select a reservation type first.');
        const elem = document.getElementById('availabilityResults');
        if (elem) elem.style.display = 'none';
        return;
    }
    
    if (!date || !time) {
        alert('Please select both date and time.');
        return;
    }
    
    // Show loading
    const elem = document.getElementById('availabilityResults');
    if (elem) elem.style.display = 'block';
    const itemSel = document.getElementById('itemSelection');
    if (itemSel) itemSel.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p>Loading available options...</p></div>';
    
    // Fetch available items
    fetch(`check_availability.php?type=${selectedType}&date=${date}&time=${time}`)
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }
        return response.text();
    })
    .then(text => {
        console.log('Response text:', text);
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            alert('Invalid response from server. Please try again.');
            const elem = document.getElementById('availabilityResults');
            if (elem) elem.style.display = 'none';
            return;
        }
        
        if (data.error) {
            alert('Error: ' + data.error);
            const elem = document.getElementById('availabilityResults');
            if (elem) elem.style.display = 'none';
            return;
        }
        
        loadItems(data);

        // Check if no items available
        if (data.length === 0) {
            const nextTime = getNextAvailableTime(time);
            const nextTimeElem = document.getElementById('nextTime');
            if (nextTimeElem) nextTimeElem.textContent = nextTime;
            const nextAvailElem = document.getElementById('nextAvailable');
            if (nextAvailElem) nextAvailElem.style.display = 'block';
        } else {
            const nextAvailElem = document.getElementById('nextAvailable');
            if (nextAvailElem) nextAvailElem.style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error: ' + error.message + '. Check console for details.');
        const elem = document.getElementById('availabilityResults');
        if (elem) elem.style.display = 'none';
    });
}

function loadItems(items) {
    const itemSelection = document.getElementById('itemSelection');

    if (items.length === 0) {
        itemSelection.innerHTML = '<div class="alert alert-warning">No available options for the selected date and time. Please try different date/time.</div>';
        document.getElementById('continueBtn').disabled = true;
        return;
    }

    // Build a responsive grid - tables in 2 columns, cottages with special handling for 5th item
    let html = '<div class="row g-3">';
    items.forEach((item, index) => {
        const name = selectedType === 'table' ? `Table ${item.table_number}` : item.cottage_name;
        const details = selectedType === 'table'
            ? `Location: ${item.location}<br>Description: ${item.description}`
            : `Amenities: ${item.amenities}<br>Description: ${item.description}`;

        // Use item.image if available
        const imgHtml = item.image ? `<img src="${item.image}" alt="${name}" style="width:100%; height:160px; object-fit:cover; border-radius:8px 8px 0 0;">` : '';

        let colClass;
        if (selectedType === 'table') {
            // Tables: always 2 columns
            colClass = 'col-12 col-sm-6';
        } else {
            // Cottages: special handling for the 5th item (Consumable Cottage) - make it span 2 columns
            const isFifthItem = index === 4; // 0-based index, so 4 is the 5th item
            colClass = isFifthItem ? 'col-12' : 'col-12 col-sm-6';
        }

        html += `
        <div class="${colClass}">
          <div class="card mb-3 item-card ${selectedType === 'cottage' && index === 4 ? 'featured-cottage' : ''}" data-id="${item.id}" onclick="selectItem(this, ${item.id})">
            ${imgHtml}
            <div class="card-body">
              <h5 class="card-title">${name}</h5>
              <p class="card-text">
                <strong>Capacity:</strong> ${item.capacity} guests<br>
                ${details}
              </p>
            </div>
          </div>
        </div>`;
    });
    html += '</div>';
    itemSelection.innerHTML = html;
}

function selectItem(elem, id) {
    // Remove previous selection
    document.querySelectorAll('.item-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Add selection to clicked item
    if (elem && elem.classList) elem.classList.add('selected');
    selectedItemId = id;
    const continueBtn = document.getElementById('continueBtn');
    if (continueBtn) continueBtn.disabled = false;
}

function continueToDetails() {
    // Set the date and time in the form
    document.getElementById('reservation_date_form').value = document.getElementById('reservation_date').value;
    document.getElementById('reservation_time_form').value = document.getElementById('reservation_time').value;
    
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
    document.getElementById('reservation_type').value = selectedType;
    document.getElementById('item_id').value = selectedItemId;
}

function goBack() {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step1').style.display = 'block';
    selectedType = '';
    selectedItemId = '';
    // Reset time picker
    document.getElementById('hourInput').value = '8';
    document.getElementById('minuteInput').value = '00';
    document.getElementById('ampmInput').value = 'AM';
    document.getElementById('reservation_time').value = '08:00:00';
    document.getElementById('continueBtn').disabled = true;
    const elem = document.getElementById('availabilityResults');
    if (elem) elem.style.display = 'none';
    const nextElem = document.getElementById('nextAvailable');
    if (nextElem) nextElem.style.display = 'none';
}

function goBackToSelection() {
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
}
</script>

<style>
.step {
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.item-card {
    cursor: pointer;
    transition: all 0.18s ease;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    position: relative;
}

.item-card:hover {
    box-shadow: 0 6px 14px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.item-card.selected {
    border: 2px solid #ffd700;
    background: linear-gradient(90deg, #fff8dc, #fff4b3);
    box-shadow: 0 8px 20px rgba(255, 215, 0, 0.12);
    transform: scale(1.02);
}

.item-card.selected .card-title {
    color: #8b0000;
    font-weight: 700;
}

/* check badge */
.item-card.selected::after {
    content: '\2713';
    position: absolute;
    top: 10px;
    right: 12px;
    background: #ffd700;
    color: #8b0000;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 800;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

/* Featured cottage styling (Consumable Cottage) */
.featured-cottage {
    border: 3px solid #ff6b35 !important;
    background: linear-gradient(135deg, #fff5f0, #ffe8e0);
    box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
}

.featured-cottage .card-title {
    color: #ff6b35;
    font-size: 1.3em;
    font-weight: 700;
}

.featured-cottage .card-body {
    background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(255, 107, 53, 0.02));
}

.featured-cottage:hover {
    box-shadow: 0 12px 30px rgba(255, 107, 53, 0.25);
    transform: translateY(-3px);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
