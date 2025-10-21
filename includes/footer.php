<footer class="footer" style="background-color: #394528;" dir="rtl">
    <div class="footer-container">
        <!-- تعريف بوح -->
        <div class="footer-section">
            <h3 style="font-size: 30px;">بوح</h3>
            <p style="color: #e0ca99; font-size: 18px;">
                مساحتك الآمنة للتعبير والدعم النفسي، نجمع بين قوة التكنولوجيا والخبرة البشرية
                لمساعدتك في رحلتك نحو الصحة النفسية.
            </p>
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <!-- روابط سريعة -->
        <div class="footer-section">
            <h3 style="color: #fff;">روابط سريعة</h3>
            <ul class="footer-links" style="font-size: 18px;">
                <li><a href="index.php" style="color: #e0ca99;">الرئيسية</a></li>
                <li><a href="#" style="color: #e0ca99;">الميزات</a></li>
                <li><a href="#" style="color: #e0ca99;">قصص النجاح</a></li>
                <li><a href="#" style="color: #e0ca99;">تحميل التطبيق</a></li>
            </ul>
        </div>

        <!-- روابط أخرى -->
        <div class="footer-section">
            <h3 style="color: #fff;">الدعم</h3>
            <ul class="footer-links" style="font-size: 18px;">
                <li><a href="#" style="color: #e0ca99;"> الأسئلة الشائعة</a></li>
                <li><a href="contact.php" style="color: #e0ca99;"> اتصل بنا</a></li>
                <li><a href="#" style="color: #e0ca99;">سياسة الخصوصية</a></li>
                <li><a href="#" style="color: #e0ca99;">شروط الاستخدام</a></li>
            </ul>
        </div>

        <!-- معلومات التواصل -->
        <div class="footer-section">
            <h3 style="color: #fff;">تواصل معنا</h3>
            <div class="contact-info" style="font-size: 18px;">
                <p style="color: #e0ca99;"><i class="fas fa-envelope"></i> boohteam@gmail.com</p>
                <p style="color: #e0ca99;"><i class="fas fa-phone"></i> +966 50 789 1234</p>
                <p style="color: #e0ca99;"><i class="fas fa-map-marker-alt"></i> الباحة - المملكة العربية السعودية</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-content" style="color: #e0ca99;">
            <p>© 2025 بوح - جميع الحقوق محفوظة</p>
        </div>
    </div>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"
    integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="./js/script.js"></script>
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

    function goToStep(step) {
        // Update session step
        <?php $_SESSION['booking']['step'] = $current_step; ?>
        window.location.href = 'booking.php';
    }

    // Set minimum date to today
    document.addEventListener('DOMContentLoaded', function () {
        const dateInput = document.getElementById('appointment_date');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
        }
    });
</script>