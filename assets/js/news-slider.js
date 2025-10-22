/**
 * اسلایدر اخبار اتوماتیک با همگام‌سازی تصاویر و عناوین
 * - اسلاید بعدی هر ۵ ثانیه
 * - اسکرول نرم عناوین بدون نمایش نوار اسکرول
 * - ریست انیمیشن progress bar در هر تغییر اسلاید
 */

document.addEventListener('DOMContentLoaded', function() {
    // جلوگیری از دوباره‌سازی فقط برای اسلایدر اخبار
    if (window.__newsSliderInitialized) return;
    window.__newsSliderInitialized = true;

    const slider = document.querySelector('.featured-slider');
    const newsList = document.querySelector('.featured-news-list');

    if (!slider || !newsList) return;

    const slides = slider.querySelectorAll('.featured-slide');
    const newsItems = newsList.querySelectorAll('.news-item');

    if (slides.length === 0 || newsItems.length === 0) return;

    let currentSlide = 0;
    let intervalId = null;

    function showSlide(nextIndex) {
        // محاسبه ایمن اندیس
        currentSlide = ((nextIndex % slides.length) + slides.length) % slides.length;

        // همگام‌سازی کلاس‌ها
        slides.forEach(slide => slide.classList.remove('active'));
        newsItems.forEach(item => item.classList.remove('active-news'));
        slides[currentSlide]?.classList.add('active');
        newsItems[currentSlide]?.classList.add('active-news');

        // اسکرول نرم لیست عناوین تا آیتم فعال (سازگارتر)
        const activeItem = newsItems[currentSlide];
        if (activeItem && typeof activeItem.scrollIntoView === 'function') {
            activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        // ریست انیمیشن نوار پیشرفت اسلاید جاری
        const progressFill = slides[currentSlide]?.querySelector('.slide-progress-fill');
        if (progressFill) {
            progressFill.style.animation = 'none';
            // reflow
            // eslint-disable-next-line no-unused-expressions
            progressFill.offsetHeight;
            progressFill.style.animation = 'progressAnimation 5s linear forwards';
        }
    }

    function start() {
        stop();
        intervalId = setInterval(() => showSlide(currentSlide + 1), 5000);
    }

    function stop() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    }

    // تعامل کاربر با عناوین
    newsItems.forEach((item, index) => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', (e) => {
            if (e.target && e.target.tagName === 'A') return; // اجازه بده روی لینک بره
            e.preventDefault();
            showSlide(index);
            start(); // با زمان‌بندی تازه ادامه بده
        });
    });

    // توقف/ادامه هنگام hover
    ['mouseenter', 'mouseleave'].forEach(evt => {
        slider.addEventListener(evt, evt === 'mouseenter' ? stop : start);
        newsList.addEventListener(evt, evt === 'mouseenter' ? stop : start);
    });

    // شروع
    showSlide(0);
    start();
    
    // Debug
    // console.log('✅ News Slider initialized with', slides.length, 'slides');
});
