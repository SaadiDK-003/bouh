<?php
session_start();
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bouh System</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body class="rtl">

  <?php include 'includes/header.php'; ?>

  <!-- Hero Section -->
  <section id="hero-section">

    <div class="d-grid grid-col-2 gap-2 w-1280">
      <div class="content-container">

      <!-- Text -->
      <div class="text-container">
        <h1><span>بوح</span> - مساحتك للتعبير والدعم النفسي</h1>
        <p>
          تطبيق يجمع بين قوة الذكاء الاصطناعي والدعم النفسي المتخصص، لمساعدتك في رحلتك نحو الصحة النفسية والعافية
        </p>

        <div class="flex-container">
          <!-- Smart assistant button -->

          <a href="#!" class="btn">
            <i class="fas fa-comment"></i>
            حدث مع المساعد الذكي
          </a>

          <!-- Book therapist button -->
          <a href="./booking.php" class="btn bg-white">
            <i class="fas fa-calendar-alt"></i>
            احجز جلسة مع معالج
          </a>
        </div>
      </div>

    </div>
    <img src="images/homePerson.png" width="800" height="600" alt="شاب مع مساعد ذكي - بوح للدعم النفسي"
      class="object-contain absolute inset-0 h-full w-full">
    </div>

  </section>

  <section id="smart-assistant">
    <div class="content-center-container">
      <div class="icon red">
        <img src="./svg/robo.svg" alt="robo">
      </div>
      <h2>المساعد الذكي للتشخيص الأولي</h2>
      <p>تحدث مع المساعد الذكي للحصول على تقييم أولي لحالتك النفسية، يساعدك في فهم مشاعرك وتوجيهك نحو الخطوات المناسبة</p>
    </div>
    <div class="grid-container d-grid grid-col-2 gap-2 w-1280 my-4">
      <div class="item">
        <div class="content-center-container">
          <div class="icon red">
            <img src="./svg/robo.svg" alt="robo">
          </div>
            <h2>جرب المساعد الذكي</h2>
            <p>يمكنك التحدث مع المساعد الذكي للحصول على تقييم أولي لحالتك النفسية وفهم أفضل لمشاعرك</p>
            <a href="#!" class="btn">ابدأ المحادثة الآن</a>
        </div>
      </div>
      <div class="item">
        <div class="box">
          <div class="icon-text">
            <h3>تشخيص أولي دقيق</h3>
            <div class="icon"><img src="./svg/right-arrow.svg" alt="right-arrow"></div>
          </div>
          <p>يستخدم المساعد الذكي نماذج متقدمة من الذكاء الاصطناعي لتحليل أعراضك وتقديم تقييم أولي دقيق لحالتك النفسية</p>
        </div>
        <div class="box">
          <div class="icon-text">
            <h3>خصوصية تامة</h3>
            <div class="icon"><img src="./svg/right-arrow.svg" alt="right-arrow"></div>
          </div>
          <p>جميع محادثاتك مع المساعد الذكي مشفرة ومحمية بالكامل، نضمن لك الخصوصية التامة وسرية معلوماتك الشخصية</p>
        </div>
        <div class="box">
          <div class="icon-text">
            <h3>تكامل مع خدمات العلاج</h3>
            <div class="icon"><img src="./svg/right-arrow.svg" alt="right-arrow"></div>
          </div>
          <p>بناءً على نتائج التشخيص الأولي، يمكن للمساعد الذكي توجيهك إلى المعالج المناسب وتحديد موعد معه مباشرة</p>
        </div>
      </div>
    </div>
  </section>

  <section id="psychological">
    <div class="content-center-container">
      <div class="icon green">
        <img src="./svg/clipboard.svg" alt="clipboard">
      </div>
      <h2>التقييمات النفسية الذاتية</h2>
      <p>اختبارات نفسية معتمدة سريرياً مبسطة ومترجمة للعربية، تساعدك على فهم مشاعرك وتوجهك نحو الخطوات المناسبة لرعاية صحتك النفسية</p>
    </div>

    <div class="d-grid grid-col-3 gap-2 my-2 w-1280">
      <div class="item min-h-340">
        <div class="icon blue">
          <img src="./svg/clipboard-blue.svg" alt="clipboard">
        </div>
        <div class="content">
          <h3>تقييم القلق (GAD-7)</h3>
          <p>اختبار قصير يساعدك على تقييم أعراض الاكتئاب لديك وشدتها، مبني على مقياس PHQ-9 المعتمد عالمياً</p>
          <span>7 أسئلة · 2 دقائق للإكمال</span>
        </div>
        <a href="#!" class="btn blue">ابدأ التقييم</a>
      </div>
      <div class="item min-h-340">
        <div class="icon orange">
          <img src="./svg/clipboard-orange.svg" alt="clipboard">
        </div>
        <div class="content">
          <h3>تقييم القلق (GAD-7)</h3>
          <p>اختبار قصير يساعدك على تقييم مستوى القلق لديك وتأثيره على حياتك، مبني على مقياس GAD-7 المعتمد سريرياً</p>
          <span>7 أسئلة · 2 دقائق للإكمال</span>
        </div>
        <a href="#!" class="btn orange">ابدأ التقييم</a>
      </div>
      <div class="item min-h-340">
        <div class="icon green">
          <img src="./svg/clipboard.svg" alt="clipboard">
        </div>
        <div class="content">
          <h3>تقييم الصحة النفسية العام</h3>
          <p>تقييم شامل يساعدك على فهم حالتك النفسية العامة ويحدد المجالات التي قد تحتاج إلى اهتمام</p>
          <span>15 سؤال · 5 دقائق للإكمال</span>
        </div>
        <a href="#!" class="btn green">ابدأ التقييم</a>
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>
  <script src="js/script.js"></script>
  <script>
    // القائمة للجوال
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navMenu.classList.toggle('active');
    });

    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
      hamburger.classList.remove('active');
      navMenu.classList.remove('active');
    }));
  </script>
</body>

</html>