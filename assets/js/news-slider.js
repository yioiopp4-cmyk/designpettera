/**
 * اسلایدر اخبار اتوماتیک با همگام‌سازی تصاویر و عناوین
 */

document.addEventListener('DOMContentLoaded', function() {
    if (window.__featuredSliderInitialized) {
        return; // جلوگیری از دوبار مقداردهی
    }
    window.__featuredSliderInitialized = true;
    
    const slider = document.querySelector('.featured-slider');
    const newsList = document.querySelector('.featured-news-list');
    
    if (!slider || !newsList) return;
    
    const slides = slider.querySelectorAll('.featured-slide');
    const newsItems = newsList.querySelectorAll('.news-item');
    
    if (slides.length === 0 || newsItems.length === 0) return;
    
    let currentSlide = 0;
    let autoplayInterval = null;
    
    // تابع تغییر اسلاید
    function changeSlide(index) {
        // حذف کلاس active از همه
        slides.forEach(slide => slide.classList.remove('active'));
        newsItems.forEach(item => item.classList.remove('active-news'));
        
        // اضافه کردن کلاس active به اسلاید و عنوان مورد نظر
        if (slides[index]) slides[index].classList.add('active');
        if (newsItems[index]) newsItems[index].classList.add('active-news');
        
        currentSlide = index;
    }
    
    // تابع رفتن به اسلاید بعدی
    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        changeSlide(nextIndex);
    }
    
    // شروع autoplay
    function startAutoplay() {
        stopAutoplay(); // ابتدا autoplay قبلی را متوقف کن
        autoplayInterval = setInterval(nextSlide, 5000); // هر 5 ثانیه
    }
    
    // توقف autoplay
    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }
    
    // کلیک روی عناوین اخبار
    newsItems.forEach((item, index) => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            changeSlide(index);
            stopAutoplay();
            setTimeout(startAutoplay, 3000); // بعد از 3 ثانیه دوباره autoplay شروع شود
        });
    });
    
    // توقف autoplay در هنگام hover
    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);
    
    newsList.addEventListener('mouseenter', stopAutoplay);
    newsList.addEventListener('mouseleave', startAutoplay);
    
    // شروع اسلایدر
    startAutoplay();
    
    console.log('✅ News Slider initialized with', slides.length, 'slides');
});
