<header class="header">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="../index.php">
                    بوح
                </a>
            </div>

            <div class="main-menu">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="../index.php" class="nav-link">الرئيسية</a>
                    </li>
                    <li class="nav-item d-none">
                        <a href="../doctors.php" class="nav-link">حجز جلسة</a>
                    </li>
                    <li class="nav-item">
                        <a href="../assessments.php" class="nav-link">اكتشف نفسك</a>
                    </li>
                    <li class="nav-item">
                        <a href="../booking.php" class="nav-link">حجز جلسة</a>
                    </li>
                    <li>
                        <a href="../#app-features" class="nav-link">المميزات</a>
                    </li>
                    <li>
                        <a href="../#success-stories" class="nav-link">آراء المستخدمين</a>
                    </li>
                    <li>
                        <a href="../#start-your-journey" class="nav-link">التحميل</a>
                    </li>
                </ul>

                <?php
                if (isset($_SESSION['is_admin'])) {
                    ?>
                    <div class="nav-auth">
                        <a href="./index.php?logout" class="btn btn-secondary">تسجيل خروج</a>
                    </div>
                <?php } ?>
            </div>

            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>
</header>