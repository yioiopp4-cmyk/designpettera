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

        // اسکرول نرم لیست عناوین تا آیتم فعال (با محاسبه اختلاف دید)
        const activeItem = newsItems[currentSlide];
        if (activeItem) {
            const c = newsList;
            const cRect = c.getBoundingClientRect();
            const iRect = activeItem.getBoundingClientRect();
            let delta = 0;
            if (iRect.top < cRect.top) {
                delta = iRect.top - cRect.top - 6; // کمی بالاتر
            } else if (iRect.bottom > cRect.bottom) {
                delta = iRect.bottom - cRect.bottom + 6; // کمی پایین‌تر
            }
            if (delta !== 0) {
                c.scrollBy({ top: delta, behavior: 'smooth' });
            }
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

    // تعامل کاربر با عناوین (بدون توقف دائمی اسلایدر)
    newsItems.forEach((item, index) => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', (e) => {
            // اگر روی لینک کلیک شد اجازه بده ناوبری انجام شود
            if (e.target && e.target.tagName === 'A') return;
            e.preventDefault();
            showSlide(index);
            start(); // بلافاصله تایمر را بازتنظیم کن
        });
        // جلوگیری از توقف با اسکرول وسط موس (wheel): فقط اسکرول عناوین انجام شود و تایمر ادامه یابد
        item.addEventListener('wheel', (e) => {
            const direction = Math.sign(e.deltaY);
            if (direction !== 0) {
                e.preventDefault();
                newsList.scrollBy({ top: direction * 40, behavior: 'smooth' });
            }
        }, { passive: false });
    });

    // دیگر در حالت hover متوقف نمی‌کنیم تا اسلایدر هرگز گیر نکند

    // در تغییر دید صفحه، اجرای تایمر را مدیریت کنیم
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
        } else {
            start();
        }
    });

    // شروع
    showSlide(0);
    start();
    
    // Debug
    // console.log('✅ News Slider initialized with', slides.length, 'slides');
});
