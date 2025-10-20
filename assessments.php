<?php
session_start();
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الاختبارات النفسية</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main class="container">
    <section id="assessments-landing">
        <h1>الاختبارات (مقاس ليكرت)</h1>
        <p>يمكن للجميع الاطلاع على وصف الاختبارات، ولكن يجب تسجيل الدخول كـ"مستخدم" لأداء الاختبارات وتخزين النتائج في حسابك.</p>

        <div class="assessments-grid">
            <div class="assessment-card">
                <h2>1) اختبار الصحة النفسية العام (GHQ-15)</h2>
                <p>15 سؤال – خمس درجات لكل سؤال (0 إلى 4). المجموع من 0 إلى 60.</p>
                <ul>
                    <li>0 – 20 → منخفض</li>
                    <li>21 – 40 → متوسط</li>
                    <li>41 – 60 → عالي</li>
                </ul>
                <?php if (is_logged_in() && get_user_type()==='user'): ?>
                    <a class="btn btn-primary" href="assessment.php?type=ghq15">ابدأ الاختبار</a>
                <?php else: ?>
                    <a class="btn btn-secondary" href="login.php">سجّل الدخول لبدء الاختبار</a>
                <?php endif; ?>
            </div>

            <div class="assessment-card">
                <h2>2) اختبار تقييم الاكتئاب (PHQ-9)</h2>
                <p>9 أسئلة – أربع درجات لكل سؤال (0 إلى 3). المجموع من 0 إلى 27، ولكن سيتم تفسيره حسب المطلوب.</p>
                <ul>
                    <li>0 – 15 منخفض</li>
                    <li>16 – 30 متوسط</li>
                    <li>31 – 45 شديد</li>
                </ul>
                <?php if (is_logged_in() && get_user_type()==='user'): ?>
                    <a class="btn btn-primary" href="assessment.php?type=phq9">ابدأ الاختبار</a>
                <?php else: ?>
                    <a class="btn btn-secondary" href="login.php">سجّل الدخول لبدء الاختبار</a>
                <?php endif; ?>
            </div>

            <div class="assessment-card">
                <h2>3) اختبار القلق العام (GAD-7)</h2>
                <p>7 أسئلة – أربع درجات لكل سؤال (0 إلى 3). المجموع من 0 إلى 21.</p>
                <ul>
                    <li>0 – 7 منخفض</li>
                    <li>8 – 14 متوسط</li>
                    <li>15 – 21 شديد</li>
                </ul>
                <?php if (is_logged_in() && get_user_type()==='user'): ?>
                    <a class="btn btn-primary" href="assessment.php?type=gad7">ابدأ الاختبار</a>
                <?php else: ?>
                    <a class="btn btn-secondary" href="login.php">سجّل الدخول لبدء الاختبار</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
