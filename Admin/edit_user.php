<?php
session_start();
require_once '../includes/config.php';

// Check if user is already logged in
// if(!is_logged_in()) {
//     redirect('index.php');
// }

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
}

$getUserQ = $conn->query("SELECT * FROM `users` WHERE `id`='$user_id'");

$usr = mysqli_fetch_object($getUserQ);

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    // $password = $_POST['password'];
    // $confirm_password = $_POST['confirm_password'];
    $user_type = sanitize_input($_POST['user_type']);

    // Validate input
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();


        // Handle photo upload
        $photo_filename = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $upload_result = upload_file($_FILES['photo'], 'uploads/');
            if ($upload_result['success']) {
                $photo_filename = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }

        if (!isset($error)) {
            // Hash password
            // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $sql = "UPDATE users SET name=?, email=?, phone=?, user_type=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $email, $phone, $user_type, $user_id);
            if ($stmt->execute()) {
                $success = "Success";
                echo '<script>setTimeout(function(){ window.location.href = "index.php"; }, 2000);</script>';
            } else {
                $error = "Registration failed. Please try again.";
            }
        }

    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب </title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .auth-card h2 {
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="text-center mb-8"
            style="text-align: center; margin-bottom: 1rem; margin-top: 3rem; font-size: 22px;">
            <h1 class="text-3xl font-bold text-olive-900 mb-2 font-arabic">إنشاء حساب جديد</h1>
            <p class="text-olive-700">انضم إلى بوح واحصل على الدعم النفسي الذي تحتاجه</p>
        </div>
        <div class="auth-container">
            <div class="auth-card edit_user_wrapper">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success text-center"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" class="auth-form edit_user" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="user_type">من انت:</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">اختر نوع المستخدم</option>
                            <option <?= $usr->user_type == 'user' ? 'selected' : '' ?> value="user">مريض</option>
                            <option <?= $usr->user_type == 'doctor' ? 'selected' : '' ?> value="doctor">طبيب</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="name">الاسم الكامل</label>
                        <input type="text" id="name" name="name" value="<?= $usr->name ?>" required
                            placeholder="أدخل اسمك الكامل">
                    </div>

                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" value="<?= $usr->email ?>" required
                            placeholder="أدخل بريدك الإلكتروني">
                    </div>

                    <div class="form-group">
                        <label for="phone">رقم الهاتف</label>
                        <input type="tel" id="phone" name="phone" value="<?= $usr->phone ?>" required
                            placeholder="أدخل رقم هاتفك">
                    </div>

                    <!-- <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input type="password" id="password" name="password" required placeholder="أدخل كلمة المرور">
                    </div>

                    <div class="form-group">
                        <label for="photo">الصورة الشخصية (اختياري)</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                    </div> -->

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">تسجيل</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>