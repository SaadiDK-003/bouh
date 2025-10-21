<?php
session_start();
require_once 'includes/config.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
}

// Check if temp_user_id is set (from initial signup)
if (!isset($_SESSION['temp_user_id'])) {
    redirect('signup.php');
}

$user_id = $_SESSION['temp_user_id'];

// Handle doctor signup form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $years_of_experience = sanitize_input($_POST['years_of_experience']);
    $about = sanitize_input($_POST['about']);
    $languages = isset($_POST['languages']) ? json_encode($_POST['languages']) : json_encode([]);
    $treatment_price = sanitize_input($_POST['treatment_price']);
    $specialty = sanitize_input($_POST['specialty']);

    // Validate input
    if (empty($years_of_experience) || empty($treatment_price) || empty($specialty)) {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($years_of_experience) || $years_of_experience < 0) {
        $error = "Years of experience must be a positive number.";
    } elseif (!is_numeric($treatment_price) || $treatment_price < 0) {
        $error = "Treatment price must be a positive number.";
    } else {
        // Insert doctor information into database
        $sql = "INSERT INTO doctors (user_id, years_of_experience, about, languages, treatment_price, specialty) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissds", $user_id, $years_of_experience, $about, $languages, $treatment_price, $specialty);

        if ($stmt->execute()) {
            // Clear temp session
            unset($_SESSION['temp_user_id']);
            redirect('login.php?success=1');
        } else {
            $error = "Doctor registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Sign Up - Bouh System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="auth-container">
            <div class="auth-card">
                <h2>استكمال تسجيل الطبيب</h2>
                <p>يرجى تقديم معلومات إضافية لاستكمال ملفك الطبي.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="doctor_signup.php" class="auth-form">
                    <div class="form-group">
                        <label for="specialty">التخصص *</label>
                        <input type="text" id="specialty" name="specialty" required
                            placeholder="على سبيل المثال، طبيب القلب، طبيب الأطفال، وما إلى ذلك.">
                    </div>

                    <div class="form-group">
                        <label for="years_of_experience">سنوات من الخبرة *</label>
                        <input type="number" id="years_of_experience" name="years_of_experience" required min="0"
                            placeholder="أدخل سنوات الخبرة">
                    </div>

                    <div class="form-group">
                        <label for="treatment_price">سعر العلاج <img src="./images/Saudi_Riyal_Symbol-1.png" alt="riyal_symbol">*</label>
                        <input type="number" id="treatment_price" name="treatment_price" required min="0" step="0.01"
                            placeholder="أدخل رسوم الاستشارة">
                    </div>

                    <div class="form-group">
                        <label for="about">عن نفسك</label>
                        <textarea id="about" name="about" rows="4"
                            placeholder="أخبرنا عن خبرتك ومؤهلاتك ونهجك في رعاية المرضى..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>اللغات المنطوقة</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="arabic">
                                <span>عربي</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="english">
                                <span>إنجليزي</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">التسجيل الكامل</button>
                    </div>
                </form>

                <div class="auth-links">
                    <p><a href="signup.php">عُد</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>