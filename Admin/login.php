<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

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

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .admin-login {
            max-width: 420px;
            margin: 60px auto;
            padding: 24px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #fff;
        }

        .admin-login h1 {
            margin-top: 0;
        }

        .admin-login .form-group {
            margin-bottom: 12px;
        }

        .admin-login label {
            display: block;
            margin-bottom: 6px;
        }

        .admin-login input {
            width: 100%;
            padding: 10px;
        }

        .admin-login .btn {
            width: 100%;
            margin-top: 12px;
        }

        .alert {
            padding: 10px;
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php include '../includes/header-admin.php'; ?>
    <div class="admin-login">
        <h1>Admin Login</h1>
        <?php if (!empty($error)): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="POST" action="">
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
    <?php include '../includes/footer.php'; ?>
</body>

</html>