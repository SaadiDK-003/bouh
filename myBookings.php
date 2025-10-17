<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in and is a doctor
if(!is_logged_in() || get_user_type() != 'doctor') {
    redirect('login.php');
}

// Get doctor ID from user ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT id FROM doctors WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if(!$doctor) {
    redirect('index.php');
}

$doctor_id = $doctor['id'];

// Handle appointment status updates
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['appointment_id']) && isset($_POST['status'])) {
    $appointment_id = sanitize_input($_POST['appointment_id']);
    $status = sanitize_input($_POST['status']);
    
    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if(in_array($status, $valid_statuses)) {
        $sql = "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $appointment_id, $doctor_id);
        $stmt->execute();
    }
}

// Fetch doctor's appointments
$appointments = [];
$sql = "SELECT a.*, d.specialty, u.name as doctor_name FROM appointments a 
        JOIN doctors d ON a.doctor_id = d.id 
        JOIN users u ON d.user_id = u.id 
        WHERE a.doctor_id = ? 
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Bouh System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="my-bookings-container">
            <h1>جلساتي</h1>
            
            <?php if(empty($appointments)): ?>
                <div class="no-appointments">
                    <p>You don't have any appointments yet.</p>
                    <a href="index.php" class="btn btn-primary">Go to Home</a>
                </div>
            <?php else: ?>
                <div class="appointments-filter">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="pending">Pending</button>
                    <button class="filter-btn" data-filter="confirmed">Confirmed</button>
                    <button class="filter-btn" data-filter="completed">Completed</button>
                    <button class="filter-btn" data-filter="cancelled">Cancelled</button>
                </div>
                
                <div class="appointments-grid">
                    <?php foreach($appointments as $appointment): ?>
                        <div class="appointment-card" data-status="<?php echo $appointment['status']; ?>">
                            <div class="appointment-header">
                                <div class="appointment-status status-<?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </div>
                                <div class="appointment-date">
                                    <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?>
                                    at <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                </div>
                            </div>
                            
                            <div class="appointment-details">
                                <div class="patient-info">
                                    <h3>Patient Information</h3>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['patient_email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['patient_phone']); ?></p>
                                </div>
                                
                                <?php if(!empty($appointment['cause_of_treatment'])): ?>
                                    <div class="treatment-info">
                                        <h3>Cause of Treatment</h3>
                                        <p><?php echo nl2br(htmlspecialchars($appointment['cause_of_treatment'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($appointment['notes'])): ?>
                                    <div class="notes-info">
                                        <h3>Additional Notes</h3>
                                        <p><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($appointment['user_id'])): ?>
                                    <?php
                                    // Fetch latest assessment results for this user (patient)
                                    $assessments_map = [];
                                    $sql_ass = "SELECT assessment_type, total_score, severity, created_at
                                                FROM assessments_results
                                                WHERE user_id = ?
                                                ORDER BY created_at DESC";
                                    $stmt_ass = $conn->prepare($sql_ass);
                                    $stmt_ass->bind_param('i', $appointment['user_id']);
                                    $stmt_ass->execute();
                                    $res_ass = $stmt_ass->get_result();
                                    while ($row_ass = $res_ass->fetch_assoc()) {
                                        if (!isset($assessments_map[$row_ass['assessment_type']])) {
                                            $assessments_map[$row_ass['assessment_type']] = $row_ass;
                                        }
                                    }
                                    ?>
                                    <div class="assessment-info">
                                        <h3>نتائج الاختبارات</h3>
                                        <ul>
                                            <li>
                                                <strong>GHQ-15:</strong>
                                                <?php if(isset($assessments_map['ghq15'])): $r=$assessments_map['ghq15']; ?>
                                                    مجموع <?php echo (int)$r['total_score']; ?> (<?php echo htmlspecialchars($r['severity']); ?>) — <?php echo htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))); ?>
                                                <?php else: ?>
                                                    لا توجد نتائج
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <strong>PHQ-9:</strong>
                                                <?php if(isset($assessments_map['phq9'])): $r=$assessments_map['phq9']; ?>
                                                    مجموع <?php echo (int)$r['total_score']; ?> (<?php echo htmlspecialchars($r['severity']); ?>) — <?php echo htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))); ?>
                                                <?php else: ?>
                                                    لا توجد نتائج
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <strong>GAD-7:</strong>
                                                <?php if(isset($assessments_map['gad7'])): $r=$assessments_map['gad7']; ?>
                                                    مجموع <?php echo (int)$r['total_score']; ?> (<?php echo htmlspecialchars($r['severity']); ?>) — <?php echo htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))); ?>
                                                <?php else: ?>
                                                    لا توجد نتائج
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                        <p>
                                            <a class="btn btn-secondary" href="doctor_patient_assessments.php?user_id=<?php echo (int)$appointment['user_id']; ?>">عرض جميع النتائج</a>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="appointment-actions">
                                <?php if(in_array($appointment['status'], ['pending','confirmed'])): ?>
                                    <a href="chat.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-outline">Open Chat</a>
                                <?php endif; ?>
                                <?php if($appointment['status'] == 'pending'): ?>
                                    <form method="POST" action="myBookings.php" class="status-form">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-success">Confirm</button>
                                    </form>
                                    <form method="POST" action="myBookings.php" class="status-form">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-danger">Cancel</button>
                                    </form>
                                <?php elseif($appointment['status'] == 'confirmed'): ?>
                                    <form method="POST" action="myBookings.php" class="status-form">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-primary">Mark as Completed</button>
                                    </form>
                                    <form method="POST" action="myBookings.php" class="status-form">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-danger">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const appointmentCards = document.querySelectorAll('.appointment-card');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    
                    appointmentCards.forEach(card => {
                        if(filter === 'all' || card.getAttribute('data-status') === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
