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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css"
    integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
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

            <?php
            $userID = get_user_id() ?? 0;
            $sql__Q = $conn->query("CALL `get_appointment_id_by_user`($userID)");
            if (mysqli_num_rows($sql__Q) > 0) {
              $appointment_ID = mysqli_fetch_object($sql__Q);
              ?>
              <a href="./chat.php?appointment_id=<?= $appointment_ID->appointment_id ?? 0 ?>" class="btn">
                <i class="fas fa-comment"></i>
                حدث مع المساعد الذكي
              </a>
              <?php
            } else {
              ?>
              <a href="#!" class="btn">
                <i class="fas fa-comment"></i>
                حدث مع المساعد الذكي
              </a>
              <?php
            }
            $sql__Q->close();
            $conn->next_result();
            ?>

            <!-- Book therapist button -->
            <a href="./booking.php" class="btn bg-white">
              <i class="fas fa-calendar-alt"></i>
              احجز جلسة مع معالج
            </a>
          </div>
        </div>

      </div>
      <img src="images/homePerson.png" alt="شاب مع مساعد ذكي - بوح للدعم النفسي"
        class="object-contain absolute inset-0 h-full w-full">
    </div>

  </section>

  <!-- Smart Assistant -->
  <section id="smart-assistant">
    <div class="content-center-container">
      <div class="icon red">
        <img src="./svg/robo.svg" alt="robo">
      </div>
      <h2>المساعد الذكي للتشخيص الأولي</h2>
      <p>تحدث مع المساعد الذكي للحصول على تقييم أولي لحالتك النفسية، يساعدك في فهم مشاعرك وتوجيهك نحو الخطوات المناسبة
      </p>
    </div>
    <div class="grid-container d-grid grid-col-2 gap-2 w-1280 my-4">
      <div class="item">
        <div class="content-center-container">
          <div class="icon red">
            <img src="./svg/robo.svg" alt="robo">
          </div>
          <h2>جرب المساعد الذكي</h2>
          <p>يمكنك التحدث مع المساعد الذكي للحصول على تقييم أولي لحالتك النفسية وفهم أفضل لمشاعرك</p>
          <?php
          $userID = get_user_id() ?? 0;
          $sql_Q = $conn->query("CALL `get_appointment_id_by_user`($userID)");
          if (mysqli_num_rows($sql_Q) > 0) {
            $appointmentID = mysqli_fetch_object($sql_Q);
            ?>
            <a href="./chat.php?appointment_id=<?= $appointmentID->appointment_id ?? 0 ?>" class="btn">ابدأ المحادثة
              الآن</a>
            <?php
          } else {
            ?>
            <a href="#!" class="btn">ابدأ المحادثة الآن</a>
            <?php
          }
          $sql_Q->close();
          $conn->next_result();
          ?>


        </div>
      </div>
      <div class="item">
        <div class="box">
          <div class="icon-text">
            <h3>تشخيص أولي دقيق</h3>
            <div class="icon"><img src="./svg/right-arrow-orange.svg" alt="right-arrow"></div>
          </div>
          <p>يستخدم المساعد الذكي نماذج متقدمة من الذكاء الاصطناعي لتحليل أعراضك وتقديم تقييم أولي دقيق لحالتك النفسية
          </p>
        </div>
        <div class="box">
          <div class="icon-text">
            <h3>خصوصية تامة</h3>
            <div class="icon"><img src="./svg/right-arrow-orange.svg" alt="right-arrow"></div>
          </div>
          <p>جميع محادثاتك مع المساعد الذكي مشفرة ومحمية بالكامل، نضمن لك الخصوصية التامة وسرية معلوماتك الشخصية</p>
        </div>
        <div class="box">
          <div class="icon-text">
            <h3>تكامل مع خدمات العلاج</h3>
            <div class="icon"><img src="./svg/right-arrow-orange.svg" alt="right-arrow"></div>
          </div>
          <p>بناءً على نتائج التشخيص الأولي، يمكن للمساعد الذكي توجيهك إلى المعالج المناسب وتحديد موعد معه مباشرة</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Psychological -->
  <section id="psychological">
    <div class="content-center-container">
      <div class="icon green">
        <img src="./svg/clipboard.svg" alt="clipboard">
      </div>
      <h2>التقييمات النفسية الذاتية</h2>
      <p>اختبارات نفسية معتمدة سريرياً مبسطة ومترجمة للعربية، تساعدك على فهم مشاعرك وتوجهك نحو الخطوات المناسبة لرعاية
        صحتك النفسية</p>
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
        <a href="./assessments.php" class="btn blue">ابدأ التقييم</a>
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
        <a href="./assessments.php" class="btn orange">ابدأ التقييم</a>
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
        <a href="./assessments.php" class="btn green">ابدأ التقييم</a>
      </div>
    </div>

    <div class="d-grid grid-4-1 w-1280 psychological__bottom-section">
      <div class="item">
        <h3>لماذا التقييمات النفسية مهمة؟</h3>
        <p>التقييمات النفسية الذاتية تساعدك على فهم مشاعرك بشكل أفضل وتحديد الخطوات المناسبة للعناية بصحتك النفسية. هذه
          الاختبارات مبنية على أدوات نفسية معتمدة عالمياً، ولكنها مبسطة ومترجمة للعربية لتكون سهلة الاستخدام.</p>
        <div class="buttons-wrapper">
          <a href="#!"><img src="./svg/right-arrow-green.svg" alt="">&nbsp; خصوصية تامة</a>
          <a href="#!"><img src="./svg/right-arrow-green.svg" alt="">&nbsp; نتائج فورية</a>
          <a href="#!"><img src="./svg/right-arrow-green.svg" alt="">&nbsp; توصيات مخصصة</a>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <img src="./svg/clipboard-red.svg" alt="clipboard-red">
        </div>
      </div>
    </div>
  </section>

  <section id="app-features">
    <div class="content-center-container">
      <h2>ميزات تطبيق <span>بوح</span></h2>
      <p>مجموعة متكاملة من الأدوات والخدمات المصممة لدعم صحتك النفسية بطريقة شاملة وفعالة</p>
    </div>
    <div class="d-grid grid-col-3 gap-2 my-2 w-1280">
      <div class="item">
        <div class="icon">
          <img src="./svg/clipboard-blue.svg" class="w-40" alt="clipboard">
        </div>
        <div class="content">
          <h3>مساعد ذكي</h3>
          <p>امحادثات مدعومة بالذكاء الاصطناعي لتقديم الدعم والاستماع على مدار الساعة
          </p>
        </div>
      </div>
      <div class="item">
        <div class="icon w-40">
          <img src="./svg/clipboard-orange.svg" class="w-40" alt="clipboard">
        </div>
        <div class="content">
          <h3>تشخيص أولي</h3>
          <p>
            تقييم مبدئي لحالتك النفسية باستخدام الذكاء الاصطناعي المتقدم لتوجيهك نحو المسار العلاجي المناسب
          </p>
        </div>
      </div>
      <div class="item">
        <div class="icon w-40">
          <img src="./svg/heart-red.svg" class="w-40" alt="heart-red">
        </div>
        <div class="content">
          <h3>رعاية شخصية </h3>
          <p>
            خطط دعم مخصصة تناسب احتياجاتك الفردية ومسارك الشخصي
          </p>
        </div>
      </div>
      <div class="item">
        <div class="icon w-40">
          <img src="./svg/heart-red.svg" class="w-40" alt="heart-red">
        </div>
        <div class="content">
          <h3>خصوصية تامة</h3>
          <p>حماية بيانات مشددة وضمان السرية التامة لجميع المحادثات والمعلومات
          </p>
        </div>
      </div>
      <div class="item">
        <div class="icon w-40">
          <img src="./svg/heart-red.svg" class="w-40" alt="heart-red">
        </div>
        <div class="content">
          <h3>تمارين يومية</h3>
          <p>
            أنشطة وتمارين للتأمل والاسترخاء لمساعدتك في التعامل مع التوتر والقلق
          </p>
        </div>
      </div>
      <div class="item">
        <div class="icon w-40">
          <img src="./svg/heart-red.svg" class="w-40" alt="heart-red">
        </div>
        <div class="content">
          <h3>جلسات احترافية</h3>
          <p>تسهولة حجز جلسات مع معالجين نفسيين معتمدين ومتخصصين
          </p>
        </div>
      </div>
    </div>
  </section>

  <section id="success-stories">
    <div class="content-center-container">
      <h2>قصص نجاح مع <span>بوح</span></h2>
      <p>استمع إلى تجارب مستخدمينا وكيف ساعدهم تطبيق بوح في رحلتهم نحو الصحة النفسية</p>
    </div>
    <div class="mt-40 stories-container w-1280">
      <div class="owl-carousel success-stories-wrapper">
        <?php
        $reviews_Q = $conn->query("CALL `get_reviews`()");
        while ($row = mysqli_fetch_object($reviews_Q)):
          ?>
          <div class="item">
            <div class="d-grid grid-4-1">
              <div class="text">
                <img src="./svg/quote-red.svg" alt="quote-red">
                <p><?= $row->comment ?></p>
                <h3><?= $row->name ?></h3>
                <span class="stars-count __<?= $row->rating ?>">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                </span>
              </div>
              <div class="img">
                <img src="./uploads/<?= $row->photo ?? '68d2fb2b63e34.jpg' ?>" alt="user-image">
              </div>
            </div>
          </div>
        <?php endwhile;
        $reviews_Q->close();
        $conn->next_result();
        ?>
      </div>
      <div class="owl-nav-custom-btns">
        <button class="owl-prev-custom-btn"><img src="./svg/right-nav.svg" alt="right-arrow"></button>
        <button class="owl-next-custom-btn"><img src="./svg/left-nav.svg" alt="left-arrow"></button>
      </div>
    </div>
  </section>

  <section id="start-your-journey">
    <div class="d-grid grid-col-2 gap-2 w-1280">
      <div class="item">
        <div class="content-center-container">
          <h2>ابدأ رحلتك نحو الصحة النفسية مع<span> بوح</span></h2>
          <p>حمّل التطبيق الآن واحصل على جلسة مجانية مع أحد معالجينا المتخصصين. خطوة صغيرة اليوم يمكن أن تحدث فرقاً
            كبيراً في حياتك غداً.</p>
        </div>
        <div class="buttons-wrapper">
          <a href="#!">Google Play</a>
          <a href="#!">App Store</a>
        </div>
        <p class="text-stars">⭐⭐⭐⭐⭐ <span>4.9</span> تقييم من أكثر من <span>10,000</span> مستخدم</p>
      </div>
      <div class="item">
        <img src="./images/placeholder.svg" alt="placeholder">
      </div>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

</body>

</html>