<header class="header">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    بوح
                </a>
            </div>

            <div class="main-menu">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a href="doctors.php" class="nav-link">حجز جلسة</a>
                    </li>
                    <li class="nav-item">
                        <a href="assessments.php" class="nav-link">اكتشف نفسك</a>
                    </li>
                    <li class="nav-item">
                        <a href="booking.php" class="nav-link">الحجوزات</a>
                    </li>
                    <li>
                        <a href="#app-features" class="nav-link">المميزات</a>
                    </li>
                    <li>
                        <a href="#success-stories" class="nav-link">آراء المستخدمين</a>
                    </li>
                    <li>
                        <a href="#start-your-journey" class="nav-link">التحميل</a>
                    </li>
                </ul>

                <div class="nav-auth">
                    <?php if (is_logged_in()): ?>
                        <div class="user-menu">
                            <span class="welcome-text">مرحباً، <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                            <?php if (get_user_type() == 'doctor'): ?>
                                <a href="myBookings.php" class="btn btn-secondary">حجوزاتي</a>
                            <?php elseif (get_user_type() == 'user'): ?>
                                <a href="userBookings.php" class="btn btn-secondary">جلساتي</a>
                            <?php endif; ?>
                            <a href="logout.php" class="btn btn-outline">تسجيل خروج</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">تسجيل دخول</a>
                        <a href="signup.php" class="btn btn-secondary">إنشاء حساب</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>
</header>

