<?php
session_start();
require_once 'includes/config.php';

// Require login
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user_type = get_user_type(); // 'user' or 'doctor'

// Validate appointment_id
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
if ($appointment_id <= 0) {
    redirect('index.php');
}

// Load appointment and authorization context
// We need doctor user_id and patient user_id to authorize access
$sql = "SELECT a.*, d.user_id AS doctor_user_id
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$appt_res = $stmt->get_result();
$appointment = $appt_res->fetch_assoc();

if (!$appointment) {
    redirect('index.php');
}

$doctor_user_id = (int)$appointment['doctor_user_id'];
$patient_user_id = $appointment['user_id'] ? (int)$appointment['user_id'] : 0;

// Authorize: only the doctor assigned to this appointment or the patient who booked it
$authorized = ($user_id === $doctor_user_id) || ($patient_user_id > 0 && $user_id === $patient_user_id);
if (!$authorized) {
    redirect('index.php');
}

// Determine if chat is active (allowed to send messages)
$chat_active = in_array($appointment['status'], ['pending', 'confirmed']);

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    if (!$chat_active) {
        // Do not allow posting when appointment is not active
        redirect('chat.php?appointment_id=' . $appointment_id);
    }
    $message = trim($_POST['message']);
    if ($message !== '') {
        $sender_type = ($user_id === $doctor_user_id) ? 'doctor' : 'user';
        $sql = "INSERT INTO chat_messages (appointment_id, sender_id, sender_type, message) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiss', $appointment_id, $user_id, $sender_type, $message);
        $stmt->execute();
    }
    // Redirect to avoid resubmission
    redirect('chat.php?appointment_id=' . $appointment_id);
}

// Fetch counterpart info for header display
if ($user_id === $doctor_user_id) {
    // Current user is doctor; show patient name/email/phone from appointment
    $counterpart_name = $appointment['patient_name'];
    $counterpart_role = 'Patient';
} else {
    // Current user is patient; show doctor name
    $sql = "SELECT u.name AS doctor_name, d.specialty FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $appointment['doctor_id']);
    $stmt->execute();
    $doc_res = $stmt->get_result();
    $doc = $doc_res->fetch_assoc();
    $counterpart_name = $doc ? $doc['doctor_name'] : 'Doctor';
    $counterpart_role = 'Doctor' . ($doc && $doc['specialty'] ? ' (' . htmlspecialchars($doc['specialty']) . ')' : '');
}

// Load chat history
$sql = "SELECT cm.*, u.name AS sender_name
        FROM chat_messages cm
        JOIN users u ON cm.sender_id = u.id
        WHERE cm.appointment_id = ?
        ORDER BY cm.created_at ASC, cm.id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $appointment_id);
$stmt->execute();
$msg_res = $stmt->get_result();
$messages = $msg_res->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Chat</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .chat-wrapper{max-width:900px;margin:20px auto;background:#fff;border:1px solid #eee;border-radius:8px;display:flex;flex-direction:column;height:70vh}
        .chat-header{padding:12px 16px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center}
        .chat-title{font-weight:600}
        .chat-status{font-size:12px;padding:4px 8px;border-radius:999px}
        .status-active{background:#e6f7ef;color:#0a7c42}
        .status-closed{background:#fdecea;color:#b10d0d}
        .chat-body{flex:1;overflow-y:auto;padding:16px;background:#fafafa}
        .msg{margin-bottom:12px;max-width:70%;padding:10px 12px;border-radius:10px;line-height:1.3}
        .msg.me{background:#e6f0ff;margin-left:auto}
        .msg.other{background:#fff;border:1px solid #eee}
        .msg .meta{font-size:11px;color:#666;margin-top:6px}
        .chat-input{display:flex;gap:8px;padding:12px;border-top:1px solid #eee}
        .chat-input textarea{flex:1;resize:vertical;min-height:40px;max-height:120px}
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="chat-wrapper">
        <div class="chat-header">
            <div>
                <div class="chat-title">Chat with <?php echo htmlspecialchars($counterpart_name); ?></div>
                <div class="chat-sub">Appointment on <?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?> at <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></div>
            </div>
            <div>
                <?php if ($chat_active): ?>
                    <span class="chat-status status-active">Active (<?php echo htmlspecialchars($appointment['status']); ?>)</span>
                <?php else: ?>
                    <span class="chat-status status-closed">Closed (<?php echo htmlspecialchars($appointment['status']); ?>)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-body" id="chatBody">
            <?php if (empty($messages)): ?>
                <p>No messages yet. Start the conversation.</p>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <?php $mine = ($m['sender_id'] == $user_id); ?>
                    <div class="msg <?php echo $mine ? 'me' : 'other'; ?>">
                        <div class="text"><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                        <div class="meta"><?php echo htmlspecialchars($m['sender_name']); ?> • <?php echo date('M d, Y h:i A', strtotime($m['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="chat-input">
            <?php if ($chat_active): ?>
                <form method="POST" action="chat.php?appointment_id=<?php echo $appointment_id; ?>" style="display:flex;gap:8px;width:100%">
                    <textarea name="message" placeholder="Type your message..." required></textarea>
                    <button type="submit" class="btn btn-primary">Send</button>
                </form>
            <?php else: ?>
                <div style="padding:8px;color:#666">Chat is closed for this appointment.</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
// Auto-scroll to bottom
const bodyEl = document.getElementById('chatBody');
if (bodyEl) { bodyEl.scrollTop = bodyEl.scrollHeight; }
// Simple auto-refresh every 10s
setInterval(() => {
    window.location.reload();
}, 10000);
</script>
</body>
</html>
