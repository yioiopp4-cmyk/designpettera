<footer class="site-footer tf-footer">
    <div class="container">
        
        <div class="footer-top">
            <div class="footer-about">
                <div class="footer-logo">
                    <svg width="48" height="48" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="18" r="18" fill="url(#footerGrad)"/>
                        <path d="M18 8L23 13L18 18L13 13L18 8Z" fill="white"/>
                        <path d="M18 18L23 23L18 28L13 23L18 18Z" fill="white" opacity="0.7"/>
                        <defs>
                            <linearGradient id="footerGrad" x1="0" y1="0" x2="36" y2="36">
                                <stop offset="0%" stop-color="#667eea"/>
                                <stop offset="100%" stop-color="#764ba2"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <h3><?php bloginfo('name'); ?></h3>
                </div>
                <p class="footer-description">
                    بهترین منبع اطلاعات برای قیمت لحظه‌ای ارزهای دیجیتال، اخبار و تحلیل‌های بازار کریپتو. ما به شما کمک می‌کنیم تا بهترین تصمیمات را در دنیای ارزهای دیجیتال بگیرید.
                </p>
                <div class="social-links">
                    <a href="#" class="social-icon" aria-label="تلگرام">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0-.005 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-icon" aria-label="توییتر">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-icon" aria-label="اینستاگرام">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" fill="#0f1419"/>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="#0f1419" stroke-width="2"/>
                        </svg>
                    </a>
                    <a href="#" class="social-icon" aria-label="لینکدین">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/>
                            <circle cx="4" cy="4" r="2"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="footer-widgets">
                <?php if (is_active_sidebar('footer-1')) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar('footer-1'); ?>
                    </div>
                <?php else : ?>
                    <div class="footer-column">
                        <h4 class="footer-title">دسترسی سریع</h4>
                        <ul class="footer-menu">
                            <li><a href="<?php echo home_url('/'); ?>">صفحه اصلی</a></li>
                            <li><a href="<?php echo home_url('/crypto-list'); ?>">قیمت ارزها</a></li>
                            <li><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">اخبار</a></li>
                            <li><a href="#">درباره ما</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (is_active_sidebar('footer-2')) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar('footer-2'); ?>
                    </div>
                <?php else : ?>
                    <div class="footer-column">
                        <h4 class="footer-title">ارزهای پرطرفدار</h4>
                        <ul class="footer-menu">
                            <li><a href="#">بیت کوین (Bitcoin)</a></li>
                            <li><a href="#">اتریوم (Ethereum)</a></li>
                            <li><a href="#">تتر (Tether)</a></li>
                            <li><a href="#">بایننس کوین (BNB)</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (is_active_sidebar('footer-3')) : ?>
                    <div class="footer-column">
                        <?php dynamic_sidebar('footer-3'); ?>
                    </div>
                <?php else : ?>
                    <div class="footer-column">
                        <h4 class="footer-title">پشتیبانی</h4>
                        <ul class="footer-menu">
                            <li><a href="#">تماس با ما</a></li>
                            <li><a href="#">سوالات متداول</a></li>
                            <li><a href="#">قوانین و مقررات</a></li>
                            <li><a href="#">حریم خصوصی</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="copyright">
                    &copy; <?php echo date('Y'); ?> 
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <?php bloginfo('name'); ?>
                    </a>
                    - تمامی حقوق محفوظ است
                </p>
                <p class="footer-note">
                    قیمت‌ها به صورت لحظه‌ای از CoinGecko دریافت می‌شوند
                </p>
            </div>
        </div>
        
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="back-to-top" aria-label="بازگشت به بالا">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 15V5M10 5L5 10M10 5L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
