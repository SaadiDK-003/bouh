<?php
session_start();
require_once 'includes/config.php';

// Only doctors can access
require_doctor();

// Required patient user_id via GET
$patient_user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($patient_user_id <= 0) {
    redirect('myBookings.php');
}

// Get current doctor_id for access control
$current_user_id = $_SESSION['user_id'];
$sql_doc = "SELECT id FROM doctors WHERE user_id = ?";
$stmt_doc = $conn->prepare($sql_doc);
$stmt_doc->bind_param('i', $current_user_id);
$stmt_doc->execute();
$doctor_row = $stmt_doc->get_result()->fetch_assoc();
if (!$doctor_row) {
    redirect('myBookings.php');
}
$doctor_id = (int)$doctor_row['id'];

// Ensure this doctor has at least one appointment with this patient
$sql_check = "SELECT 1 FROM appointments WHERE doctor_id = ? AND user_id = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('ii', $doctor_id, $patient_user_id);
$stmt_check->execute();
$has_relation = $stmt_check->get_result()->fetch_row();
if (!$has_relation) {
    // No relation; deny access
    redirect('myBookings.php');
}

// Optional filters
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$valid_types = ['','ghq15','phq9','gad7'];
if (!in_array($type, $valid_types)) {
    $type = '';
}
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

// Fetch patient basic info
$sql_user = "SELECT id, name, email, phone FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $patient_user_id);
$stmt_user->execute();
$patient = $stmt_user->get_result()->fetch_assoc();
if (!$patient) {
    redirect('myBookings.php');
}

// Build query for assessments
$query = "SELECT assessment_type, total_score, severity, created_at FROM assessments_results WHERE user_id = ?";
$params = [$patient_user_id];
$types = 'i';

if ($type !== '') {
    $query .= " AND assessment_type = ?";
    $params[] = $type;
    $types .= 's';
}

if ($from !== '') {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $from;
    $types .= 's';
}

if ($to !== '') {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $to;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج اختبارات المريض</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="container">
    <h1>نتائج اختبارات المريض</h1>
    <div class="patient-summary" style="margin-bottom:16px;">
        <p><strong>الاسم:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
        <p><strong>البريد:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
        <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
    </div>

    <form method="GET" action="doctor_patient_assessments.php" class="filters" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <input type="hidden" name="user_id" value="<?php echo (int)$patient_user_id; ?>">
        <div>
            <label for="type">الاختبار</label>
            <select id="type" name="type">
                <option value="" <?php echo $type===''?'selected':''; ?>>الكل</option>
                <option value="ghq15" <?php echo $type==='ghq15'?'selected':''; ?>>GHQ-15</option>
                <option value="phq9" <?php echo $type==='phq9'?'selected':''; ?>>PHQ-9</option>
                <option value="gad7" <?php echo $type==='gad7'?'selected':''; ?>>GAD-7</option>
            </select>
        </div>
        <div>
            <label for="from">من تاريخ</label>
            <input type="date" id="from" name="from" value="<?php echo htmlspecialchars($from); ?>">
        </div>
        <div>
            <label for="to">إلى تاريخ</label>
            <input type="date" id="to" name="to" value="<?php echo htmlspecialchars($to); ?>">
        </div>
        <div style="align-self:end;">
            <button type="submit" class="btn btn-primary">تصفية</button>
            <a class="btn btn-secondary" href="doctor_patient_assessments.php?user_id=<?php echo (int)$patient_user_id; ?>">إعادة تعيين</a>
        </div>
    </form>

    <?php if (empty($results)): ?>
        <div class="alert">لا توجد نتائج مطابقة للمعايير المحددة.</div>
    <?php else: ?>
        <div class="results-list">
            <?php foreach ($results as $row): ?>
                <div class="result-item" style="border:1px solid #ddd;padding:12px;margin-bottom:10px;border-radius:6px;">
                    <div><strong>الاختبار:</strong> <?php echo strtoupper(htmlspecialchars($row['assessment_type'])); ?></div>
                    <div><strong>المجموع:</strong> <?php echo (int)$row['total_score']; ?></div>
                    <div><strong>الشدة:</strong> <?php echo htmlspecialchars($row['severity']); ?></div>
                    <div><strong>التاريخ:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><a class="btn btn-outline" href="myBookings.php">عودة إلى حجوزاتي</a></p>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
