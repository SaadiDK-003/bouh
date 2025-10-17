<?php
session_start();
require_once 'includes/config.php';

// Check if user is already logged in
if(is_logged_in()) {
    redirect('index.php');
}

// Handle login form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if(empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check user credentials
        $sql = "SELECT id, name, email, password, user_type FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if(password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if($user['user_type'] == 'doctor') {
                    redirect('myBookings.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - بوح  </title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: "Cairo", "Tahoma", sans-serif;
            direction: rtl;
            text-align: right;
        }
        .auth-card h2 {
            text-align: center;
        }
    </style>
</head>
<body style="background-color: #faf8f0;">
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="text-center mb-8" style="text-align: center; margin-top: 3rem; font-size: 22px;">
            <h1 class="text-3xl font-bold text-olive-900 font-arabic">تسجيل الدخول إلى بوح</h1>
            <p class="text-olive-700">مرحباً بعودتك، سعداء برؤيتك مجدداً</p>
        </div>
        <div class="auth-container">
            <div class="auth-card">
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success">تم التسجيل بنجاح! يرجى تسجيل الدخول.</div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" class="auth-form">
                    <h2 class="text-xl font-bold text-olive-800 text-right font-arabic" style="margin-bottom: 0rem;  text-align: right;">من أنت؟</h2>
                    <div class="form-group">
                        <label for="email">البريد الإلكتروني</label>
                        <input type="email" id="email" name="email" required placeholder="أدخل بريدك الإلكتروني">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input type="password" id="password" name="password" required placeholder="أدخل كلمة المرور">
                    </div>
                    
                    <div class="form-group">
                        <button style="background-color: #c94530;color: white;" type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
                    </div>
                </form>
                
                <div class="auth-links">
                    <p>  ليس لديك حساب؟ <a href="signup.php">إنشاء حساب جديد</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
