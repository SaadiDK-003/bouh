<?php
session_start();
require_once 'includes/config.php';

// Only logged-in users (patients)
if (!is_logged_in() || get_user_type() !== 'user') {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle cancel request by user (allowed if not completed or cancelled)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $appointment_id = (int) $_POST['appointment_id'];

    // Ensure this appointment belongs to this user and is cancellable
    $sql = "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending','confirmed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $appointment_id, $user_id);
    $stmt->execute();
}

// Handle review submit/update by user (allowed when status is pending or confirmed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id']) && isset($_POST['action']) && $_POST['action'] === 'review') {
    $appointment_id = (int) $_POST['appointment_id'];
    $rating = isset($_POST['rating']) ? max(0, min(5, (int) $_POST['rating'])) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    // Validate ownership and status
    $sql = "SELECT id, doctor_id, status FROM appointments WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $appointment_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (in_array($row['status'], ['pending', 'confirmed'])) {
            // Upsert into doctor_reviews (unique per appointment)
            $doctor_id = (int) $row['doctor_id'];
            // Check existing
            $chk = $conn->prepare("SELECT id FROM doctor_reviews WHERE appointment_id = ?");
            $chk->bind_param('i', $appointment_id);
            $chk->execute();
            $exists = $chk->get_result()->fetch_assoc();
            if ($exists) {
                $upd = $conn->prepare("UPDATE doctor_reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE appointment_id = ?");
                $upd->bind_param('isi', $rating, $comment, $appointment_id);
                $upd->execute();
            } else {
                $ins = $conn->prepare("INSERT INTO doctor_reviews (appointment_id, doctor_id, user_id, rating, comment) VALUES (?,?,?,?,?)");
                $ins->bind_param('iiiis', $appointment_id, $doctor_id, $user_id, $rating, $comment);
                $ins->execute();
            }
        }
    }
}

// Fetch user's appointments
$appointments = [];
$sql = "SELECT a.*, d.specialty, udoc.name AS doctor_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users udoc ON d.user_id = udoc.id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);

// Prefetch reviews for these appointments
$reviews_by_appt = [];
if (!empty($appointments)) {
    $ids = array_column($appointments, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt_r = $conn->prepare("SELECT appointment_id, rating, comment, created_at, updated_at FROM doctor_reviews WHERE appointment_id IN ($placeholders)");
    $stmt_r->bind_param($types, ...$ids);
    $stmt_r->execute();
    $res_r = $stmt_r->get_result();
    while ($r = $res_r->fetch_assoc()) {
        $reviews_by_appt[$r['appointment_id']] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جلساتي - Bouh System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="my-bookings-container">
            <h1>جلساتي</h1>

            <?php if (empty($appointments)): ?>
                <div class="no-appointments">
                    <p>You don't have any appointments yet.</p>
                    <a href="booking.php" class="btn btn-primary">Book an Appointment</a>
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
                    <?php foreach ($appointments as $appointment): ?>
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
                                <div class="doctor-info">
                                    <h3>طبيب</h3>
                                    <p><strong>اسم:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                                    <p><strong>التخصص:</strong> <?php echo htmlspecialchars($appointment['specialty']); ?>
                                    </p>
                                </div>
                                <div class="treatment-notes">
                                    <?php if (!empty($appointment['cause_of_treatment'])): ?>
                                        <div class="treatment-info">
                                            <h3>سبب العلاج</h3>
                                            <p><?php echo nl2br(htmlspecialchars($appointment['cause_of_treatment'])); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($appointment['notes'])): ?>
                                        <div class="notes-info">
                                            <h3>ملاحظات إضافية</h3>
                                            <p><?php echo nl2br(htmlspecialchars($appointment['notes'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                // Fetch latest assessments for this user (same user for all cards)
                                $assessments_map = [];
                                $sql_ass = "SELECT assessment_type, total_score, severity, created_at
                                        FROM assessments_results
                                        WHERE user_id = ?
                                        ORDER BY created_at DESC";
                                $stmt_ass = $conn->prepare($sql_ass);
                                $stmt_ass->bind_param('i', $user_id);
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
                                            <?php if (isset($assessments_map['ghq15'])):
                                                $r = $assessments_map['ghq15']; ?>
                                                مجموع <?php echo (int) $r['total_score']; ?>
                                                (<?php echo htmlspecialchars($r['severity']); ?>) —
                                                <?php echo htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))); ?>
                                            <?php else: ?>
                                                لا توجد نتائج
                                            <?php endif; ?>
                                            <a class="btn btn-outline" style="margin-right:8px;"
                                                href="assessment.php?type=ghq15">أداء GHQ-15</a>
                                        </li>
                                        <li>
                                            <strong>PHQ-9:</strong>
                                            <?php if (isset($assessments_map['phq9'])):
                                                $r = $assessments_map['phq9']; ?>
                                                مجموع <?php echo (int) $r['total_score']; ?>
                                                (<?php echo htmlspecialchars($r['severity']); ?>) —
                                                <?php echo htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))); ?>
                                            <?php else: ?>
                                                لا توجد نتائج
                                            <?php endif; ?>
                                            <a class="btn btn-outline" style="margin-right:8px;"
                                                href="assessment.php?type=phq9">أداء PHQ-9</a>
                                        </li>
                                        <li>
                                            <strong>GAD-7:</strong>
                                            <?php if (isset($assessments_map['gad7'])):
                                                $r = $assessments_map['gad7']; ?>
                                                مجموع <?php echo (int) $r['total_score']; ?>
                                                (<?php echo htmlspecialchars($r['severity']); ?>) —
                                                <?php echo htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))); ?>
                                            <?php else: ?>
                                                لا توجد نتائج
                                            <?php endif; ?>
                                            <a class="btn btn-outline" style="margin-right:8px;"
                                                href="assessment.php?type=gad7">أداء GAD-7</a>
                                        </li>
                                    </ul>
                                    <p><a class="btn btn-secondary" href="assessments.php">تعرف على الاختبارات</a></p>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <div class="buttons-wrapper">
                                    <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                                        <a href="chat.php?appointment_id=<?php echo $appointment['id']; ?>"
                                            class="btn btn-outline">افتح الدردشة</a>
                                    <?php endif; ?>

                                    <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" action="userBookings.php" class="status-form"
                                            onsubmit="return confirm('Cancel this appointment?');">
                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn btn-danger">إلغاء الموعد</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="review-section">
                                    <h3>تقييمك للطبيب</h3>
                                    <?php $existing = $reviews_by_appt[$appointment['id']] ?? null; ?>
                                    <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" action="userBookings.php" class="review-form">
                                            <input type="hidden" name="appointment_id"
                                                value="<?php echo (int) $appointment['id']; ?>">
                                            <input type="hidden" name="action" value="review">
                                            <label>التقييم (0-5):
                                                <select name="rating" required>
                                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                                        <option value="<?php echo $i; ?>" <?php echo ($existing && (int) $existing['rating'] === $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </label>
                                            <label>تعليق:
                                                <textarea name="comment" rows="2"
                                                    placeholder="اكتب رأيك..."><?php echo $existing ? htmlspecialchars($existing['comment']) : ''; ?></textarea>
                                            </label>
                                            <button type="submit" class="btn btn-primary">حفظ التقييم</button>
                                        </form>
                                    <?php else: ?>
                                        <?php if ($existing): ?>
                                            <p><strong>التقييم:</strong> <?php echo (int) $existing['rating']; ?>/5</p>
                                            <?php if (!empty($existing['comment'])): ?>
                                                <p><strong>تعليقك:</strong> <?php echo nl2br(htmlspecialchars($existing['comment'])); ?></p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p>لا يوجد تقييم لهذا الحجز.</p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
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
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const cards = document.querySelectorAll('.appointment-card');

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const filter = this.getAttribute('data-filter');
                    cards.forEach(c => {
                        c.style.display = (filter === 'all' || c.getAttribute('data-status') === filter) ? 'block' : 'none';
                    });
                });
            });
        });
    </script>
</body>

</html>