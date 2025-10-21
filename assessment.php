<?php
session_start();
require_once 'includes/config.php';

// Only logged-in users (patients) can take assessments
if (!is_logged_in() || get_user_type() !== 'user') {
    redirect('login.php');
}

$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$valid_types = ['ghq15', 'phq9', 'gad7'];
if (!in_array($type, $valid_types)) {
    redirect('assessments.php');
}

// Define questions and scoring map per assessment
$questions = [];
$options = [];

if ($type === 'ghq15') {
    $questions = [
        'أشعر بالرضا عن حياتي بشكل عام',
        'أستطيع التعامل مع ضغوط الحياة اليومية',
        'أشعر بالارتباط مع الآخرين وأملك علاقات داعمه',
        'أشعر بالقلق معظم الوقت',
        'أجد صعوبة في النوم أو البقاء نائما',
        'أشعر بالحزن أو الاكتئاب',
        'اجد صعوبة في التركيز على المهام',
        'أشعر بالإرهاق أو نقص الطاقة',
        'استمتع بالأنشطة التي كنت أستمتع بها سابقاً',
        'أشعر بالأمل تجاه المستقبل',
        'أشعر بالغضب أو الانفعال بسهولة',
        'أفكر في إيذاء نفسي',
        'أستخدم الكحول أو المخدرات للتعامل مع مشاعري',
        'أشعر بالقيمة والتقدير',
        'أعتني بصحتي البدنية',
    ];
    $options = [
        0 => 'لا أوافق بشدة',
        1 => 'لا أوافق',
        2 => 'محايد',
        3 => 'أوافق',
        4 => 'أوافق بشدة',
    ];
} elseif ($type === 'phq9') {
    $questions = [
        'قلة الأهتمام أو قلة الاستمتاع بممارسة الأشياء',
        'الشعور بالحزن أو الاكتئاب أو اليأس',
        'صعوبة في النوم أو النوم المتقطع أو النوم أكثر من المعتاد',
        'الشعور بالتعب أو وجود القليل من الطاقة',
        'قلة الشهية أو الإفراط في تناول الطعام',
        'الشعور بعدم الرضا عن النفس أو الشعور بالفشل أو خذلان نفسك أوعائلتك',
        'صعوبة في التركيز على الأشياء، مثل قراءة الصحف أو مشاهدة التلفزيون',
        'التحرك أو التحدث ببطء لدرجة أن الآخرين قد لاحظوا ذلك، أو العكس: الشعور بالتململ أو عدم الأستقرار',
        'أفكار بأنه من الأفضل لو كنت ميتاً أو أفكار بإيذاء نفسك بطريقة ما',
    ];
    $options = [
        0 => 'إطلاقاً',
        1 => 'عدة أيام',
        2 => 'أكثر من نصف الأيام',
        3 => 'تقريباً كل يوم',
    ];
} else { // gad7
    $questions = [
        'الشعور بالعصبية أو القلق أو التوتر',
        'عدم القدرة على إيقاف أو السيطرة على القلق',
        'القلق الزائد حول أمور مختلفة',
        'صعوبة في الاسترخاء',
        'الانزعاج لدرجة يصعب معها الجلوس بهدوء',
        'سرعة الانفعال أو الغضب',
        'الشعور بالخوف كما لو أن شيئا فظيعا قد يحدث',
    ];
    $options = [
        0 => 'إطلاقاً',
        1 => 'عدة أيام',
        2 => 'أكثر من نصف الأيام',
        3 => 'تقريباً كل يوم',
    ];
}

$user_id = $_SESSION['user_id'];
$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect answers
    $answers = [];
    $total = 0;
    for ($i = 1; $i <= count($questions); $i++) {
        $key = 'q' . $i;
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            $error_message = 'يرجى الإجابة على جميع الأسئلة.';
            break;
        }
        $val = (int) $_POST[$key];
        if (!array_key_exists($val, $options)) {
            $error_message = 'قيمة غير صالحة.';
            break;
        }
        $answers[$key] = $val;
        $total += $val;
    }

    if (!$error_message) {
        // Determine severity per provided interpretation
        $severity = '';
        if ($type === 'ghq15') {
            if ($total <= 20)
                $severity = 'منخفض';
            elseif ($total <= 40)
                $severity = 'متوسط';
            else
                $severity = 'عالي';
        } elseif ($type === 'phq9') {
            if ($total <= 15)
                $severity = 'منخفض';
            elseif ($total <= 30)
                $severity = 'متوسط';
            else
                $severity = 'شديد';
        } else { // gad7
            if ($total <= 7)
                $severity = 'منخفض';
            elseif ($total <= 14)
                $severity = 'متوسط';
            else
                $severity = 'شديد';
        }

        // Save to DB
        $sql = "INSERT INTO assessments_results (user_id, assessment_type, total_score, severity, answers) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $answers_json = json_encode($answers, JSON_UNESCAPED_UNICODE);
        $stmt->bind_param('isiss', $user_id, $type, $total, $severity, $answers_json);
        if ($stmt->execute()) {
            $success_message = 'تم حفظ نتائج الاختبار بنجاح. مجموعك: ' . $total . ' (' . $severity . ').';
        } else {
            $error_message = 'حدث خطأ أثناء حفظ النتائج.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الاختبار</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main class="container">
        <div class="assessment-form">
            <h1>
                <?php if ($type === 'ghq15')
                    echo 'اختبار الصحة النفسية العام (GHQ-15)';
                elseif ($type === 'phq9')
                    echo 'اختبار تقييم الاكتئاب (PHQ-9)';
                else
                    echo 'اختبار القلق (GAD-7)'; ?>
            </h1>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                    <p>
                        <a class="btn btn-primary" href="assessments.php">العودة إلى الاختبارات</a>
                        <a class="btn btn-secondary" href="userBookings.php">عرض جلساتي</a>
                    </p>
                </div>
            <?php else: ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <form method="POST" action="assessment.php?type=<?php echo htmlspecialchars($type); ?>">
                    <ol>
                        <?php foreach ($questions as $idx => $q):
                            $qnum = $idx + 1; ?>
                            <li style="margin-bottom:16px;">
                                <div class="question-text" style="margin-bottom:6px; font-weight:bold;">
                                    <?php echo htmlspecialchars($q); ?>
                                </div>
                                <div class="options">
                                    <?php foreach ($options as $val => $label): ?>
                                        <label style="margin-left:12px;">
                                            <input type="radio" name="q<?php echo $qnum; ?>" value="<?php echo $val; ?>" required>
                                            <?php echo htmlspecialchars($label); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="buttons-wrapper">
                        <a href="./assessments.php" class="btn-back">عُد</a>
                        <button type="submit" class="btn btn-primary">إرسال</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
</body>

</html>