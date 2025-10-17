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

  </section>

  <section id="smart-assistant">
    <div class="content-center-container">
      <div class="icon red">
        <img src="./svg/robo.svg" alt="robo">
      </div>
      <h2>المساعد الذكي للتشخيص الأولي</h2>
      <p>تحدث مع المساعد الذكي للحصول على تقييم أولي لحالتك النفسية، يساعدك في فهم مشاعرك وتوجيهك نحو الخطوات المناسبة</p>
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