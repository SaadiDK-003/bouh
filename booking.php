<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php');
}

// Initialize booking session
if (!isset($_SESSION['booking'])) {
    $_SESSION['booking'] = [
        'step' => 1,
        'doctor_id' => null,
        'appointment_date' => null,
        'appointment_time' => null,
        'patient_name' => null,
        'patient_email' => null,
        'patient_phone' => null,
        'cause_of_treatment' => null,
        'notes' => null
    ];
}

// Handle form submissions for each step
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $step = isset($_POST['step']) ? (int) $_POST['step'] : 1;

    switch ($step) {
        case 1:
            // Step 1: Choose doctor
            $doctor_id = sanitize_input($_POST['doctor_id']);
            if (!empty($doctor_id)) {
                $_SESSION['booking']['doctor_id'] = $doctor_id;
                $_SESSION['booking']['step'] = 2;
            }
            break;

        case 2:
            // Step 2: Schedule appointment
            $appointment_date = sanitize_input($_POST['appointment_date']);
            $appointment_time = sanitize_input($_POST['appointment_time']);
            if (!empty($appointment_date) && !empty($appointment_time)) {
                $_SESSION['booking']['appointment_date'] = $appointment_date;
                $_SESSION['booking']['appointment_time'] = $appointment_time;
                $_SESSION['booking']['step'] = 3;
            }
            break;

        case 3:
            // Step 3: Patient data
            $patient_name = sanitize_input($_POST['patient_name']);
            $patient_email = sanitize_input($_POST['patient_email']);
            $patient_phone = sanitize_input($_POST['patient_phone']);
            $cause_of_treatment = sanitize_input($_POST['cause_of_treatment']);
            $notes = sanitize_input($_POST['notes']);

            if (!empty($patient_name) && !empty($patient_email) && !empty($patient_phone)) {
                $_SESSION['booking']['patient_name'] = $patient_name;
                $_SESSION['booking']['patient_email'] = $patient_email;
                $_SESSION['booking']['patient_phone'] = $patient_phone;
                $_SESSION['booking']['cause_of_treatment'] = $cause_of_treatment;
                $_SESSION['booking']['notes'] = $notes;
                $_SESSION['booking']['step'] = 4;
            }
            break;

        case 4:
            // Step 4: Complete booking
            if (isset($_SESSION['booking']['doctor_id']) && isset($_SESSION['booking']['appointment_date'])) {
                $sql = "INSERT INTO appointments (doctor_id, user_id, patient_name, patient_email, patient_phone, appointment_date, appointment_time, cause_of_treatment, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "iisssssss",
                    $_SESSION['booking']['doctor_id'],
                    $_SESSION['user_id'],
                    $_SESSION['booking']['patient_name'],
                    $_SESSION['booking']['patient_email'],
                    $_SESSION['booking']['patient_phone'],
                    $_SESSION['booking']['appointment_date'],
                    $_SESSION['booking']['appointment_time'],
                    $_SESSION['booking']['cause_of_treatment'],
                    $_SESSION['booking']['notes']
                );

                if ($stmt->execute()) {
                    // Clear booking session
                    unset($_SESSION['booking']);
                    redirect('booking.php?success=1');
                } else {
                    $error = "Booking failed. Please try again.";
                }
            }
            break;
    }
}

// Get current step
$current_step = isset($_SESSION['booking']['step']) ? $_SESSION['booking']['step'] : 1;

// Fetch doctors for step 1
$doctors = [];
if ($current_step == 1) {
    $sql = "SELECT d.*, u.name, u.email, u.phone, u.photo FROM doctors d JOIN users u ON d.user_id = u.id";
    $result = $conn->query($sql);
    if ($result) {
        $doctors = $result->fetch_all(MYSQLI_ASSOC);
    }
    // Prefetch ratings and recent comments for all doctors
    $ratings_map = [];
    $comments_map = [];
    if (!empty($doctors)) {
        $doctor_ids = array_column($doctors, 'id');
        $placeholders = implode(',', array_fill(0, count($doctor_ids), '?'));
        $types = str_repeat('i', count($doctor_ids));
        // Average rating and count
        $stmtR = $conn->prepare("SELECT doctor_id, AVG(rating) AS avg_rating, COUNT(*) AS cnt FROM doctor_reviews WHERE doctor_id IN ($placeholders) GROUP BY doctor_id");
        $stmtR->bind_param($types, ...$doctor_ids);
        $stmtR->execute();
        $resR = $stmtR->get_result();
        while ($row = $resR->fetch_assoc()) {
            $ratings_map[$row['doctor_id']] = [
                'avg' => round((float) $row['avg_rating'], 2),
                'count' => (int) $row['cnt']
            ];
        }
        // All comments per doctor (ordered newest first)
        $stmtC = $conn->prepare("SELECT doctor_id, comment, rating, created_at FROM doctor_reviews WHERE doctor_id IN ($placeholders) AND comment IS NOT NULL AND comment <> '' ORDER BY created_at DESC");
        $stmtC->bind_param($types, ...$doctor_ids);
        $stmtC->execute();
        $resC = $stmtC->get_result();
        while ($c = $resC->fetch_assoc()) {
            $did = (int) $c['doctor_id'];
            if (!isset($comments_map[$did])) {
                $comments_map[$did] = [];
            }
            $comments_map[$did][] = $c;
        }
    }
}

// Get selected doctor info
$selected_doctor = null;
if (isset($_SESSION['booking']['doctor_id'])) {
    $sql = "SELECT d.*, u.name, u.email, u.phone, u.photo FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['booking']['doctor_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_doctor = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Bouh System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="booking-container">
            <h1>حجز جلسة مع معالج نفسي</h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <h3>Booking Successful!</h3>
                    <p>Your appointment has been booked successfully. You will receive a confirmation email shortly.</p>
                    <a href="index.php" class="btn btn-primary">Go to Home</a>
                </div>
            <?php else: ?>
                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="step <?php echo $current_step >= 1 ? 'active' : ''; ?>">
                        <div class="step-number">1</div>
                        <div class="step-title">اختيار المعالج</div>
                    </div>
                    <div class="step <?php echo $current_step >= 2 ? 'active' : ''; ?>">
                        <div class="step-number">2</div>
                        <div class="step-title">جدول</div>
                    </div>
                    <div class="step <?php echo $current_step >= 3 ? 'active' : ''; ?>">
                        <div class="step-number">3</div>
                        <div class="step-title">معلومات المريض</div>
                    </div>
                    <div class="step <?php echo $current_step >= 4 ? 'active' : ''; ?>">
                        <div class="step-number">4</div>
                        <div class="step-title">مراجعة</div>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Step 1: Choose Doctor -->
                <?php if ($current_step == 1): ?>
                    <div class="booking-step">
                        <h2>الخطوة  1: اختر الطبيب</h2>
                        <form method="POST" action="booking.php" class="doctor-selection">
                            <input type="hidden" name="step" value="1">
                            <div class="doctors-grid">
                                <?php foreach ($doctors as $doctor): ?>
                                    <label for="doctor_<?php echo (int) $doctor['id']; ?>">
                                        <div class="doctor-card">
                                            <div class="doctor-info">
                                                <?php if ($doctor['photo']): ?>
                                                    <img src="uploads/<?php echo htmlspecialchars($doctor['photo']); ?>"
                                                        alt="<?php echo htmlspecialchars($doctor['name']); ?>" class="doctor-photo">
                                                <?php else: ?>
                                                    <div class="doctor-avatar">
                                                        <i class="fas fa-user-md"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="doctor-details">
                                                    <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                                    <p class="specialty"><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                                                    <p class="experience"><?php echo (int) $doctor['years_of_experience']; ?> years
                                                        experience</p>
                                                    <p class="price">
                                                        $<?php echo number_format(num: $doctor['treatment_price'], decimals: 2); ?>
                                                    </p>
                                                    <?php $r = $ratings_map[$doctor['id']] ?? null; ?>
                                                    <p class="rating">Rating: <?php echo $r ? $r['avg'] : 'N/A'; ?>
                                                        (<?php echo $r ? $r['count'] : 0; ?> reviews)</p>
                                                    <?php if (!empty($comments_map[$doctor['id']])): ?>
                                                        <div class="recent-comments d-none">
                                                            <strong>All comments:</strong>
                                                            <ul>
                                                                <?php foreach ($comments_map[$doctor['id']] as $cm): ?>
                                                                    <li>
                                                                        <span>(<?php echo (int) $cm['rating']; ?>/5)</span>
                                                                        <?php echo htmlspecialchars($cm['comment']); ?>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="doctor-selection">
                                                <input type="radio" name="doctor_id" value="<?php echo (int) $doctor['id']; ?>"
                                                    id="doctor_<?php echo (int) $doctor['id']; ?>" required>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">الخطوة التالية</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Step 2: Schedule Appointment -->
                <?php if ($current_step == 2 && $selected_doctor): ?>
                    <div class="booking-step">
                        <h2>الخطوة  2: تحديد موعد</h2>
                        <div class="selected-doctor">
                            <h3>دكتور مختار: <?php echo htmlspecialchars($selected_doctor['name']); ?></h3>
                            <p><?php echo htmlspecialchars($selected_doctor['specialty']); ?> -
                                $<?php echo number_format($selected_doctor['treatment_price'], 2); ?></p>
                        </div>
                        <form method="POST" action="booking.php" class="schedule-form">
                            <input type="hidden" name="step" value="2">
                            <div class="form-group">
                                <label for="appointment_date">تاريخ التعيين</label>
                                <input type="date" id="appointment_date" name="appointment_date" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="appointment_time">وقت التعيين</label>
                                <select id="appointment_time" name="appointment_time" required>
                                    <option value="">Select time</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="goToStep(1)">سابق</button>
                                <button type="submit" class="btn btn-primary">الخطوة التالية</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Step 3: Patient Information -->
                <?php if ($current_step == 3 && $selected_doctor): ?>
                    <div class="booking-step">
                        <h2>الخطوة 3: معلومات المريض</h2>
                        <form method="POST" action="booking.php" class="patient-form">
                            <input type="hidden" name="step" value="3">
                            <div class="form-group">
                                <label for="patient_name">Full Name *</label>
                                <input type="text" id="patient_name" name="patient_name" required
                                    placeholder="Enter patient's full name">
                            </div>
                            <div class="form-group">
                                <label for="patient_email">Email *</label>
                                <input type="email" id="patient_email" name="patient_email" required
                                    placeholder="Enter email address">
                            </div>
                            <div class="form-group">
                                <label for="patient_phone">Phone *</label>
                                <input type="tel" id="patient_phone" name="patient_phone" required
                                    placeholder="Enter phone number">
                            </div>
                            <div class="form-group">
                                <label for="cause_of_treatment">Cause of Treatment</label>
                                <textarea id="cause_of_treatment" name="cause_of_treatment" rows="3"
                                    placeholder="Describe the reason for the appointment..."></textarea>
                            </div>
                            <div class="form-group">
                                <label for="notes">Additional Notes</label>
                                <textarea id="notes" name="notes" rows="3"
                                    placeholder="Any additional information..."></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="goToStep(2)">Previous</button>
                                <button type="submit" class="btn btn-primary">الخطوة التالية</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Step 4: Review and Complete -->
                <?php if ($current_step == 4 && $selected_doctor): ?>
                    <div class="booking-step">
                        <h2>Step 4: Review Booking</h2>
                        <div class="booking-review">
                            <div class="review-section">
                                <h3>Doctor Information</h3>
                                <div class="review-item">
                                    <strong>Name:</strong> <?php echo htmlspecialchars($selected_doctor['name']); ?>
                                </div>
                                <div class="review-item">
                                    <strong>Specialty:</strong> <?php echo htmlspecialchars($selected_doctor['specialty']); ?>
                                </div>
                                <div class="review-item">
                                    <strong>Consultation Fee:</strong>
                                    $<?php echo number_format($selected_doctor['treatment_price'], 2); ?>
                                </div>
                            </div>

                            <div class="review-section">
                                <h3>Appointment Details</h3>
                                <div class="review-item">
                                    <strong>Date:</strong>
                                    <?php echo date('F d, Y', strtotime($_SESSION['booking']['appointment_date'])); ?>
                                </div>
                                <div class="review-item">
                                    <strong>Time:</strong>
                                    <?php echo date('h:i A', strtotime($_SESSION['booking']['appointment_time'])); ?>
                                </div>
                            </div>

                            <div class="review-section">
                                <h3>Patient Information</h3>
                                <div class="review-item">
                                    <strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['booking']['patient_name']); ?>
                                </div>
                                <div class="review-item">
                                    <strong>Email:</strong>
                                    <?php echo htmlspecialchars($_SESSION['booking']['patient_email']); ?>
                                </div>
                                <div class="review-item">
                                    <strong>Phone:</strong>
                                    <?php echo htmlspecialchars($_SESSION['booking']['patient_phone']); ?>
                                </div>
                                <?php if (!empty($_SESSION['booking']['cause_of_treatment'])): ?>
                                    <div class="review-item">
                                        <strong>Cause of Treatment:</strong>
                                        <?php echo htmlspecialchars($_SESSION['booking']['cause_of_treatment']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($_SESSION['booking']['notes'])): ?>
                                    <div class="review-item">
                                        <strong>Notes:</strong> <?php echo htmlspecialchars($_SESSION['booking']['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <form method="POST" action="booking.php" class="booking-complete">
                            <input type="hidden" name="step" value="4">
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="goToStep(3)">Previous</button>
                                <button type="submit" class="btn btn-primary">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>