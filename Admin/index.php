<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
// Logout support

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header('Location: index.php');
    exit;
}

// Fetch all users
$users = [];
$resUsers = $conn->query("SELECT id, name, email, phone, user_type, created_at FROM users ORDER BY created_at DESC");
if ($resUsers) {
    $users = $resUsers->fetch_all(MYSQLI_ASSOC);
}

// Fetch all bookings with doctor + user info
$sqlBookings = "SELECT a.*, d.specialty, du.name AS doctor_name, uu.name AS user_name
                FROM appointments a
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users du ON d.user_id = du.id
                LEFT JOIN users uu ON a.user_id = uu.id
                ORDER BY a.created_at DESC";
$bookings = [];
$resBookings = $conn->query($sqlBookings);
if ($resBookings) {
    $bookings = $resBookings->fetch_all(MYSQLI_ASSOC);
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    if (delete_user($user_id)) {
        // Redirect to avoid resubmission
        redirect('Admin/index.php');
    } else {
        $error = "Failed to delete user.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 12px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
        }

        .table th,
        .table td {
            border: 1px solid #eee;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background: #fafafa;
        }

        .section {
            margin-bottom: 32px;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            background: #eee;
        }

        .status-pending {
            background: #fff3cd;
        }

        .status-confirmed {
            background: #cfe2ff;
        }

        .status-cancelled {
            background: #f8d7da;
        }

        .status-completed {
            background: #d1e7dd;
        }
    </style>
</head>

<body>
    <?php include '../includes/header-admin.php'; ?>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo (int) $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo htmlspecialchars($u['user_type']); ?></td>
                            <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo (int) $u['id']; ?>"
                                    class="btn btn-sm btn-primary">Edit</a>
                                <a href="?id=<?php echo (int) $u['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure to delete this user?');">Delete</a>
                            </td>
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
                            <td><?php echo (int) $b['id']; ?></td>
                            <td><?php echo htmlspecialchars($b['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($b['appointment_time']); ?></td>
                            <td><span
                                    class="badge status-<?php echo htmlspecialchars($b['status']); ?>"><?php echo htmlspecialchars($b['status']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($b['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['specialty']); ?></td>
                            <td><?php echo $b['user_name'] ? htmlspecialchars($b['user_name']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($b['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['patient_email']); ?><br><?php echo htmlspecialchars($b['patient_phone']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>