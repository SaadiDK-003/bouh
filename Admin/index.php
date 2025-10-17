<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Very simple admin auth using hardcoded credentials
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Handle login submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        if ($username === 'admin' && $password === 'admin') {
            $_SESSION['is_admin'] = true;
            // Avoid form resubmission
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }

    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin Login</title>
        <link rel="stylesheet" href="../css/style.css" />
        <style>
            .admin-login { max-width: 420px; margin: 60px auto; padding: 24px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; }
            .admin-login h1 { margin-top: 0; }
            .admin-login .form-group { margin-bottom: 12px; }
            .admin-login label { display: block; margin-bottom: 6px; }
            .admin-login input { width: 100%; padding: 10px; }
            .admin-login .btn { width: 100%; margin-top: 12px; }
            .alert { padding: 10px; color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="admin-login">
            <h1>Admin Login</h1>
            <?php if (!empty($error)): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST" action="index.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required />
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required />
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Logout support
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header('Location: index.php');
    exit;
}

// Fetch all users
$users = [];
$resUsers = $conn->query("SELECT id, name, email, phone, user_type, created_at FROM users ORDER BY created_at DESC");
if ($resUsers) { $users = $resUsers->fetch_all(MYSQLI_ASSOC); }

// Fetch all bookings with doctor + user info
$sqlBookings = "SELECT a.*, d.specialty, du.name AS doctor_name, uu.name AS user_name
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users du ON d.user_id = du.id
                LEFT JOIN users uu ON a.user_id = uu.id
                ORDER BY a.created_at DESC";
$bookings = [];
$resBookings = $conn->query($sqlBookings);
if ($resBookings) { $bookings = $resBookings->fetch_all(MYSQLI_ASSOC); }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1200px; margin: 20px auto; padding: 0 12px; }
        .admin-header { display:flex; justify-content: space-between; align-items:center; margin-bottom: 16px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        .table th { background: #fafafa; }
        .section { margin-bottom: 32px; }
        .badge { padding: 2px 6px; border-radius: 4px; background: #eee; }
        .status-pending { background:#fff3cd; }
        .status-confirmed { background:#cfe2ff; }
        .status-cancelled { background:#f8d7da; }
        .status-completed { background:#d1e7dd; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <div>
                <a href="?logout=1" class="btn btn-outline">Logout</a>
            </div>
        </div>

        <div class="section">
            <h2>All Users (<?php echo count($users); ?>)</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo (int)$u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo htmlspecialchars($u['user_type']); ?></td>
                            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>All Bookings (<?php echo count($bookings); ?>)</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Doctor</th>
                        <th>Specialty</th>
                        <th>User</th>
                        <th>Patient</th>
                        <th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?php echo (int)$b['id']; ?></td>
                            <td><?php echo htmlspecialchars($b['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($b['appointment_time']); ?></td>
                            <td><span class="badge status-<?php echo htmlspecialchars($b['status']); ?>"><?php echo htmlspecialchars($b['status']); ?></span></td>
                            <td><?php echo htmlspecialchars($b['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['specialty']); ?></td>
                            <td><?php echo $b['user_name'] ? htmlspecialchars($b['user_name']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($b['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['patient_email']); ?><br><?php echo htmlspecialchars($b['patient_phone']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
