<?php
/**
 * پنل تنظیمات قالب کریپتو سخیاب
 *
 * @package CryptoSekhyab
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * افزودن منوی تنظیمات
 */
function crypto_sekhyab_add_theme_options_menu() {
    add_menu_page(
        'تنظیمات قالب کریپتو سخیاب',
        'تنظیمات قالب',
        'manage_options',
        'crypto-sekhyab-options',
        'crypto_sekhyab_theme_options_page',
        'dashicons-bitcoin',
        60
    );
}
add_action('admin_menu', 'crypto_sekhyab_add_theme_options_menu');

/**
 * ثبت تنظیمات
 */
function crypto_sekhyab_register_settings() {
    // بخش تنظیمات صفحه اصلی
    add_settings_section(
        'crypto_sekhyab_homepage_settings',
        '🏠 تنظیمات صفحه اصلی',
        'crypto_sekhyab_homepage_settings_callback',
        'crypto-sekhyab-options'
    );
    
    // نمایش اسلایدر اخبار
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_news_slider');
    add_settings_field(
        'crypto_sekhyab_show_news_slider',
        'نمایش اسلایدر اخبار',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_homepage_settings',
        array('label_for' => 'crypto_sekhyab_show_news_slider', 'description' => 'اسلایدر اخبار در بالای صفحه اصلی نمایش داده شود')
    );
    
    // نمایش جدول ارزها
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_crypto_table');
    add_settings_field(
        'crypto_sekhyab_show_crypto_table',
        'نمایش جدول قیمت ارزها',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_homepage_settings',
        array('label_for' => 'crypto_sekhyab_show_crypto_table', 'description' => 'جدول قیمت ارزهای دیجیتال نمایش داده شود')
    );
    
    // نمایش ارزهای ترند
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_trending');
    add_settings_field(
        'crypto_sekhyab_show_trending',
        'نمایش ارزهای ترند',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_homepage_settings',
        array('label_for' => 'crypto_sekhyab_show_trending', 'description' => 'بخش ارزهای ترند امروز نمایش داده شود')
    );
    
    // نمایش بخش دیفای
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_defi_tab');
    add_settings_field(
        'crypto_sekhyab_show_defi_tab',
        'نمایش تب دیفای',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_homepage_settings',
        array('label_for' => 'crypto_sekhyab_show_defi_tab', 'description' => 'تب "دیفای" در جدول قیمت ارزها نمایش داده شود')
    );
    
    // نمایش دکمه‌های ثبت‌نام/ورود
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_auth_buttons');
    add_settings_field(
        'crypto_sekhyab_show_auth_buttons',
        'نمایش دکمه‌های ثبت‌نام/ورود',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_homepage_settings',
        array('label_for' => 'crypto_sekhyab_show_auth_buttons', 'description' => 'دکمه‌های ثبت‌نام و ورود در هدر نمایش داده شود')
    );
    
    // نمایش آخرین اخبار
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_latest_news');
    add_settings_field(
        'crypto_sekhyab_show_latest_news',
        'نمایش آخرین اخبار',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_homepage_settings',
        array('label_for' => 'crypto_sekhyab_show_latest_news', 'description' => 'لیست آخرین اخبار در پایین صفحه نمایش داده شود')
    );
    
    // بخش تنظیمات API
    add_settings_section(
        'crypto_sekhyab_api_settings',
        '🔌 تنظیمات API',
        'crypto_sekhyab_api_settings_callback',
        'crypto-sekhyab-options'
    );
    
    // نرخ تبدیل تتر (خودکار از نوبیتکس)
    // این فیلد حذف شد - نرخ به صورت خودکار دریافت می‌شود
    
    // بخش تنظیمات نمایش
    add_settings_section(
        'crypto_sekhyab_display_settings',
        '🎨 تنظیمات نمایش',
        'crypto_sekhyab_display_settings_callback',
        'crypto-sekhyab-options'
    );
    
    // نمایش price ticker
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_price_ticker');
    add_settings_field(
        'crypto_sekhyab_show_price_ticker',
        'نمایش تیکر قیمت',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_display_settings',
        array('label_for' => 'crypto_sekhyab_show_price_ticker', 'description' => 'نوار تیکر قیمت در بالای سایت نمایش داده شود')
    );
    
    // حالت تاریک
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_dark_mode');
    add_settings_field(
        'crypto_sekhyab_dark_mode',
        'حالت تاریک',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_display_settings',
        array('label_for' => 'crypto_sekhyab_dark_mode', 'description' => 'فعال‌سازی حالت تاریک برای سایت (بزودی)')
    );
    
    // تعداد ارزهای ترند
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_trending_count');
    add_settings_field(
        'crypto_sekhyab_trending_count',
        'تعداد ارزهای ترند',
        'crypto_sekhyab_number_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_display_settings',
        array('label_for' => 'crypto_sekhyab_trending_count', 'description' => 'تعداد ارزهای ترند برای نمایش (پیش‌فرض: 6)', 'default' => '6', 'min' => '3', 'max' => '12')
    );
    
    // تعداد اخبار در اسلایدر
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_slider_news_count');
    add_settings_field(
        'crypto_sekhyab_slider_news_count',
        'تعداد اخبار در اسلایدر',
        'crypto_sekhyab_number_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_display_settings',
        array('label_for' => 'crypto_sekhyab_slider_news_count', 'description' => 'تعداد اخبار در اسلایدر صفحه اصلی (پیش‌فرض: 5)', 'default' => '5', 'min' => '3', 'max' => '10')
    );
    
    // فعال‌سازی کش
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_enable_cache');
    add_settings_field(
        'crypto_sekhyab_enable_cache',
        'فعال‌سازی کش',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_display_settings',
        array('label_for' => 'crypto_sekhyab_enable_cache', 'description' => 'کش کردن قیمت‌ها برای افزایش سرعت (توصیه می‌شود)')
    );

    // بخش تنظیمات صفحه ارز
    add_settings_section(
        'crypto_sekhyab_single_coin_settings',
        '🪙 تنظیمات صفحه ارز',
        'crypto_sekhyab_single_coin_settings_callback',
        'crypto-sekhyab-options'
    );

    // نمایش لینک‌های سریع صرافی‌ها
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_exchange_links');
    add_settings_field(
        'crypto_sekhyab_show_exchange_links',
        'نمایش لینک سریع صرافی‌ها',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_show_exchange_links', 'description' => 'نمایش/عدم نمایش باکس لینک‌های سریع در سایدبار صفحه ارز')
    );

    // لیست صرافی‌ها (هر خط: Name|URL)
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_exchange_links');
    add_settings_field(
        'crypto_sekhyab_exchange_links',
        'لیست صرافی‌ها',
        'crypto_sekhyab_textarea_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_exchange_links', 'description' => 'هر خط به‌صورت Name|URL. مثال: Binance|https://www.binance.com')
    );

    // بنر تبلیغاتی
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_ad_banner');
    add_settings_field(
        'crypto_sekhyab_show_ad_banner',
        'نمایش بنر تبلیغاتی',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_show_ad_banner', 'description' => 'نمایش/عدم نمایش بنر تبلیغاتی در سایدبار')
    );

    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_ad_banner_text');
    add_settings_field(
        'crypto_sekhyab_ad_banner_text',
        'متن بنر تبلیغاتی',
        'crypto_sekhyab_text_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_ad_banner_text', 'description' => 'متن نمایشی روی بنر (مثلاً: دانلود اپلیکیشن)')
    );

    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_ad_banner_url');
    add_settings_field(
        'crypto_sekhyab_ad_banner_url',
        'لینک بنر تبلیغاتی',
        'crypto_sekhyab_text_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_ad_banner_url', 'description' => 'لینک مقصد کلیک روی بنر')
    );

    // محل نمایش بنر
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_ad_show_single_crypto');
    add_settings_field(
        'crypto_sekhyab_ad_show_single_crypto',
        'نمایش بنر در صفحه ارز',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_ad_show_single_crypto', 'description' => 'اگر فعال باشد، بنر در صفحه تک‌ارز نمایش داده می‌شود')
    );

    // بخش تنظیمات دکمه خرید ارزها
    add_settings_section(
        'crypto_sekhyab_buy_button_settings',
        '🛒 تنظیمات دکمه خرید ارزها',
        'crypto_sekhyab_buy_button_settings_callback',
        'crypto-sekhyab-options'
    );

    // فعال/غیرفعال کردن دکمه خرید
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_buy_button');
    add_settings_field(
        'crypto_sekhyab_show_buy_button',
        'نمایش دکمه خرید',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_buy_button_settings',
        array('label_for' => 'crypto_sekhyab_show_buy_button', 'description' => 'نمایش/عدم نمایش دکمه خرید در جدول قیمت ارزها')
    );

    // لینک پیش‌فرض خرید
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_default_buy_link');
    add_settings_field(
        'crypto_sekhyab_default_buy_link',
        'لینک پیش‌فرض خرید',
        'crypto_sekhyab_text_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_buy_button_settings',
        array('label_for' => 'crypto_sekhyab_default_buy_link', 'description' => 'لینک پیش‌فرض برای خرید ارزها (مثلاً: https://binance.com). {symbol} جایگزین نماد ارز می‌شود')
    );

    // لینک‌های مخصوص هر ارز
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_custom_buy_links');
    add_settings_field(
        'crypto_sekhyab_custom_buy_links',
        'لینک‌های مخصوص هر ارز',
        'crypto_sekhyab_textarea_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_buy_button_settings',
        array('label_for' => 'crypto_sekhyab_custom_buy_links', 'description' => 'هر خط: نماد ارز|لینک. مثال: BTC|https://binance.com/en/trade/BTC_USDT')
    );

    // تب تاریخچه
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_show_history_tab');
    add_settings_field(
        'crypto_sekhyab_show_history_tab',
        'نمایش تب تاریخچه',
        'crypto_sekhyab_checkbox_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_single_coin_settings',
        array('label_for' => 'crypto_sekhyab_show_history_tab', 'description' => 'تب تاریخچه برای ارز نمایش داده شود')
    );
    
    // تنظیمات دسترسی سریع
    add_settings_section(
        'crypto_sekhyab_quick_access_settings',
        '⚡ تنظیمات دسترسی سریع',
        'crypto_sekhyab_quick_access_settings_callback',
        'crypto-sekhyab-options'
    );
    
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_quick_access_items');
    add_settings_field(
        'crypto_sekhyab_quick_access_items',
        'آیتم‌های دسترسی سریع',
        'crypto_sekhyab_quick_access_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_quick_access_settings',
        array('label_for' => 'crypto_sekhyab_quick_access_items', 'description' => 'مدیریت آیتم‌های منوی دسترسی سریع')
    );
    
    // تنظیمات صفحه اخبار
    add_settings_section(
        'crypto_sekhyab_news_page_settings',
        '📰 تنظیمات صفحه اخبار',
        'crypto_sekhyab_news_page_settings_callback',
        'crypto-sekhyab-options'
    );
    
    register_setting('crypto_sekhyab_options', 'crypto_sekhyab_news_categories');
    add_settings_field(
        'crypto_sekhyab_news_categories',
        'دسته‌بندی‌های صفحه اخبار',
        'crypto_sekhyab_news_categories_field',
        'crypto-sekhyab-options',
        'crypto_sekhyab_news_page_settings',
        array('label_for' => 'crypto_sekhyab_news_categories', 'description' => 'مدیریت دسته‌بندی‌های فیلتر صفحه اخبار')
    );
}
add_action('admin_init', 'crypto_sekhyab_register_settings');

/**
 * Callback برای تنظیمات دسترسی سریع
 */
function crypto_sekhyab_quick_access_settings_callback() {
    echo '<p>مدیریت آیتم‌های منوی دسترسی سریع در هدر</p>';
}

/**
 * Callback برای تنظیمات صفحه اخبار
 */
function crypto_sekhyab_news_page_settings_callback() {
    echo '<p>مدیریت دسته‌بندی‌های فیلتر در صفحه اخبار</p>';
}

/**
 * فیلد دسترسی سریع
 */
function crypto_sekhyab_quick_access_field($args) {
    $items = get_option($args['label_for'], array(
        array('icon' => '₿', 'title' => 'بیت کوین', 'link' => '#'),
        array('icon' => '₮', 'title' => 'تتر', 'link' => '#'),
        array('icon' => 'Ξ', 'title' => 'اتریوم', 'link' => '#'),
        array('icon' => '📋', 'title' => 'لیست ارزها', 'link' => home_url('/all-cryptocurrencies')),
        array('icon' => '🔄', 'title' => 'تبدیل ارز', 'link' => '#'),
        array('icon' => '📈', 'title' => 'نمودار سود', 'link' => '#'),
    ));
    ?>
    <div id="quick-access-items">
        <?php foreach ($items as $index => $item) : ?>
            <div class="quick-access-item" style="background: #fff; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][icon]" value="<?php echo esc_attr($item['icon']); ?>" placeholder="آیکون" style="width: 60px;">
                    <input type="text" name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][title]" value="<?php echo esc_attr($item['title']); ?>" placeholder="عنوان" style="width: 200px;">
                    <input type="text" name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][link]" value="<?php echo esc_attr($item['link']); ?>" placeholder="لینک" style="width: 300px;">
                    <button type="button" class="button remove-item" onclick="this.parentElement.parentElement.remove()">حذف</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" onclick="addQuickAccessItem()">افزودن آیتم جدید</button>
    <p class="description"><?php echo esc_html($args['description']); ?></p>
    
    <script>
    function addQuickAccessItem() {
        var container = document.getElementById('quick-access-items');
        var index = container.children.length;
        var html = '<div class="quick-access-item" style="background: #fff; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;">' +
            '<div style="display: flex; gap: 10px; align-items: center;">' +
            '<input type="text" name="crypto_sekhyab_quick_access_items[' + index + '][icon]" placeholder="آیکون" style="width: 60px;">' +
            '<input type="text" name="crypto_sekhyab_quick_access_items[' + index + '][title]" placeholder="عنوان" style="width: 200px;">' +
            '<input type="text" name="crypto_sekhyab_quick_access_items[' + index + '][link]" placeholder="لینک" style="width: 300px;">' +
            '<button type="button" class="button remove-item" onclick="this.parentElement.parentElement.remove()">حذف</button>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
    }
    </script>
    <?php
}

/**
 * فیلد دسته‌بندی‌های صفحه اخبار
 */
function crypto_sekhyab_news_categories_field($args) {
    $categories = get_option($args['label_for'], array(
        array('slug' => 'all', 'name' => 'همه اخبار', 'icon' => '📰', 'color' => '#6366f1'),
        array('slug' => 'bitcoin', 'name' => 'بیت‌کوین', 'icon' => '₿', 'color' => '#f7931a'),
        array('slug' => 'ethereum', 'name' => 'اتریوم', 'icon' => '◈', 'color' => '#627eea'),
        array('slug' => 'altcoin', 'name' => 'آلت‌کوین‌ها', 'icon' => '🪙', 'color' => '#10b981'),
        array('slug' => 'analysis', 'name' => 'تحلیل', 'icon' => '📊', 'color' => '#8b5cf6'),
        array('slug' => 'global', 'name' => 'اخبار جهانی', 'icon' => '🌍', 'color' => '#06b6d4'),
        array('slug' => 'market', 'name' => 'بازار', 'icon' => '📈', 'color' => '#ec4899'),
    ));
    ?>
    <div id="news-categories-items">
        <?php foreach ($categories as $index => $cat) : ?>
            <div class="news-category-item" style="background: #fff; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" 
                           name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][slug]" 
                           value="<?php echo esc_attr($cat['slug']); ?>" 
                           placeholder="اسلاگ (slug)" 
                           style="width: 120px;"
                           <?php echo $cat['slug'] === 'all' ? 'readonly' : ''; ?>>
                    <input type="text" 
                           name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][name]" 
                           value="<?php echo esc_attr($cat['name']); ?>" 
                           placeholder="نام دسته‌بندی" 
                           style="width: 180px;">
                    <input type="text" 
                           name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][icon]" 
                           value="<?php echo esc_attr($cat['icon']); ?>" 
                           placeholder="آیکون" 
                           style="width: 70px;">
                    <input type="text" 
                           name="<?php echo esc_attr($args['label_for']); ?>[<?php echo $index; ?>][color]" 
                           value="<?php echo esc_attr($cat['color']); ?>" 
                           placeholder="رنگ (hex)" 
                           style="width: 100px;">
                    <?php if ($cat['slug'] !== 'all') : ?>
                        <button type="button" class="button remove-item" onclick="this.parentElement.parentElement.remove()">حذف</button>
                    <?php endif; ?>
                </div>
                <?php if ($index === 0) : ?>
                    <p class="description" style="margin-top: 5px; font-size: 12px;">⚠️ دسته "همه اخبار" نمی‌تواند حذف یا تغییر اسلاگ داده شود</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" onclick="addNewsCategoryItem()">افزودن دسته‌بندی جدید</button>
    <p class="description"><?php echo esc_html($args['description']); ?></p>
    <p class="description" style="margin-top: 10px;">
        <strong>نکته:</strong> اسلاگ باید با دسته‌بندی‌های (Categories) وردپرس شما مطابقت داشته باشد.<br>
        برای مثال: اگر دسته‌بندی با اسم "بیتکوین" و اسلاگ "bitcoin" در وردپرس دارید، همان اسلاگ را اینجا وارد کنید.
    </p>
    
    <script>
    function addNewsCategoryItem() {
        var container = document.getElementById('news-categories-items');
        var index = container.children.length;
        var html = '<div class="news-category-item" style="background: #fff; padding: 15px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;">' +
            '<div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">' +
            '<input type="text" name="crypto_sekhyab_news_categories[' + index + '][slug]" placeholder="اسلاگ (slug)" style="width: 120px;">' +
            '<input type="text" name="crypto_sekhyab_news_categories[' + index + '][name]" placeholder="نام دسته‌بندی" style="width: 180px;">' +
            '<input type="text" name="crypto_sekhyab_news_categories[' + index + '][icon]" placeholder="آیکون" style="width: 70px;">' +
            '<input type="text" name="crypto_sekhyab_news_categories[' + index + '][color]" placeholder="رنگ (hex)" style="width: 100px;">' +
            '<button type="button" class="button remove-item" onclick="this.parentElement.parentElement.remove()">حذف</button>' +
            '</div></div>';
        container.insertAdjacentHTML('beforeend', html);
    }
    </script>
    <?php
}
add_action('admin_init', 'crypto_sekhyab_register_settings');

/**
 * فیلد Number
 */
function crypto_sekhyab_number_field($args) {
    $option = get_option($args['label_for'], $args['default'] ?? '');
    $min = $args['min'] ?? '0';
    $max = $args['max'] ?? '100';
    ?>
    <input type="number" 
           id="<?php echo esc_attr($args['label_for']); ?>" 
           name="<?php echo esc_attr($args['label_for']); ?>" 
           value="<?php echo esc_attr($option); ?>" 
           min="<?php echo esc_attr($min); ?>"
           max="<?php echo esc_attr($max); ?>"
           class="small-text">
    <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
}

/**
 * Callback برای بخش تنظیمات صفحه اصلی
 */
function crypto_sekhyab_homepage_settings_callback() {
    echo '<p>تنظیم نمایش یا عدم نمایش بخش‌های مختلف صفحه اصلی</p>';
}

/**
 * Callback برای بخش تنظیمات API
 */
function crypto_sekhyab_api_settings_callback() {
    echo '<p>تنظیمات مربوط به API و اطلاعات قیمت</p>';
}

/**
 * Callback برای بخش تنظیمات نمایش
 */
function crypto_sekhyab_display_settings_callback() {
    echo '<p>تنظیمات مربوط به ظاهر و نمایش سایت</p>';
}

/**
 * فیلد Checkbox
 */
function crypto_sekhyab_checkbox_field($args) {
    $option = get_option($args['label_for'], '1');
    ?>
    <label>
        <input type="checkbox" 
               id="<?php echo esc_attr($args['label_for']); ?>" 
               name="<?php echo esc_attr($args['label_for']); ?>" 
               value="1" 
               <?php checked($option, '1'); ?>>
        <?php echo esc_html($args['description']); ?>
    </label>
    <?php
}

/**
 * فیلد Text
 */
function crypto_sekhyab_text_field($args) {
    $option = get_option($args['label_for'], $args['default'] ?? '');
    ?>
    <input type="text" 
           id="<?php echo esc_attr($args['label_for']); ?>" 
           name="<?php echo esc_attr($args['label_for']); ?>" 
           value="<?php echo esc_attr($option); ?>" 
           class="regular-text">
    <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
}

/**
 * فیلد Textarea
 */
function crypto_sekhyab_textarea_field($args) {
    $option = get_option($args['label_for'], $args['default'] ?? '');
    ?>
    <textarea 
        id="<?php echo esc_attr($args['label_for']); ?>"
        name="<?php echo esc_attr($args['label_for']); ?>"
        rows="6" class="large-text code"><?php echo esc_textarea($option); ?></textarea>
    <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
}

/**
 * Callback برای بخش تنظیمات صفحه ارز
 */
function crypto_sekhyab_single_coin_settings_callback() {
    echo '<p>تنظیمات اختصاصی صفحه تک‌ارز (سایدبار، بنر، تب‌ها).</p>';
}

/**
 * توضیحات بخش دکمه خرید
 */
function crypto_sekhyab_buy_button_settings_callback() {
    echo '<p>تنظیمات دکمه خرید در جدول قیمت ارزها. می‌توانید لینک پیش‌فرض یا لینک مخصوص هر ارز را تعیین کنید.</p>';
}

/**
 * صفحه تنظیمات قالب
 */
function crypto_sekhyab_theme_options_page() {
    ?>
    <div class="wrap crypto-sekhyab-settings">
        <h1>⚙️ تنظیمات قالب کریپتو سخیاب</h1>
        <p class="description">مدیریت تنظیمات قالب و نمایش بخش‌های مختلف سایت</p>
        
        <?php settings_errors('crypto_sekhyab_messages'); ?>
        
        <div class="settings-container">
            <form method="post" action="options.php" class="settings-form">
                <?php
                settings_fields('crypto_sekhyab_options');
                do_settings_sections('crypto-sekhyab-options');
                submit_button('💾 ذخیره تنظیمات');
                ?>
            </form>
            
            <div class="settings-sidebar">
                <div class="settings-box">
                    <h3>📊 اطلاعات سیستم</h3>
                    <ul class="info-list">
                        <li>
                            <strong>نسخه قالب:</strong>
                            <?php echo CRYPTO_SEKHYAB_VERSION; ?>
                        </li>
                        <li>
                            <strong>نسخه وردپرس:</strong>
                            <?php echo get_bloginfo('version'); ?>
                        </li>
                        <li>
                            <strong>نسخه PHP:</strong>
                            <?php echo phpversion(); ?>
                        </li>
                        <li>
                            <strong>حالت Debug:</strong>
                            <?php echo WP_DEBUG ? '✅ فعال' : '❌ غیرفعال'; ?>
                        </li>
                    </ul>
                </div>
                
                <div class="settings-box">
                    <h3>🔗 لینک‌های مفید</h3>
                    <ul class="links-list">
                        <li><a href="<?php echo admin_url('admin.php?page=crypto-sekhyab-logs'); ?>">مشاهده لاگ‌ها</a></li>
                        <li><a href="<?php echo home_url('/crypto-list'); ?>" target="_blank">لیست ارزها</a></li>
                        <li><a href="<?php echo home_url(); ?>" target="_blank">صفحه اصلی</a></li>
                    </ul>
                </div>
                
                <div class="settings-box">
                    <h3>💡 راهنما</h3>
                    <p>برای فعال/غیرفعال کردن هر بخش، کافی است چک‌باکس مربوطه را انتخاب یا لغو انتخاب کنید.</p>
                    <p><strong>توجه:</strong> پس از تغییرات، حتماً دکمه "ذخیره تنظیمات" را بزنید.</p>
                </div>
            </div>
        </div>
        
        <style>
            .crypto-sekhyab-settings {
                background: #f8fafc;
                padding: 20px;
            }
            
            .crypto-sekhyab-settings h1 {
                color: #0f172a;
                margin-bottom: 10px;
            }
            
            .settings-container {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 20px;
                margin-top: 20px;
            }
            
            .settings-form {
                background: #ffffff;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }
            
            .settings-form h2 {
                font-size: 18px;
                font-weight: 700;
                color: #0f172a;
                margin: 30px 0 15px 0;
                padding: 10px 15px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #ffffff;
                border-radius: 8px;
            }
            
            .settings-form h2:first-child {
                margin-top: 0;
            }
            
            .settings-form table {
                margin-top: 20px;
            }
            
            .settings-form th {
                font-weight: 600;
                color: #475569;
                padding: 15px 10px;
            }
            
            .settings-form td {
                padding: 15px 10px;
            }
            
            .settings-form input[type="checkbox"] {
                width: 20px;
                height: 20px;
                margin-left: 10px;
                cursor: pointer;
            }
            
            .settings-form label {
                display: flex;
                align-items: center;
                cursor: pointer;
                font-size: 14px;
                color: #64748b;
            }
            
            .settings-form .regular-text {
                padding: 10px 15px;
                border: 2px solid #e2e8f0;
                border-radius: 8px;
                font-size: 14px;
                width: 100%;
                max-width: 400px;
            }
            
            .settings-form .regular-text:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .settings-form .description {
                margin-top: 5px;
                font-size: 13px;
                color: #94a3b8;
            }
            
            .settings-form .submit {
                margin-top: 20px;
            }
            
            .settings-form .button-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                padding: 12px 30px;
                font-size: 15px;
                font-weight: 600;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                transition: all 0.3s ease;
            }
            
            .settings-form .button-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
            }
            
            .settings-sidebar {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
            .settings-box {
                background: #ffffff;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }
            
            .settings-box h3 {
                font-size: 16px;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 15px 0;
            }
            
            .info-list,
            .links-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .info-list li,
            .links-list li {
                padding: 8px 0;
                border-bottom: 1px solid #f1f5f9;
                font-size: 14px;
                color: #64748b;
            }
            
            .info-list li:last-child,
            .links-list li:last-child {
                border-bottom: none;
            }
            
            .info-list strong {
                color: #475569;
                margin-left: 5px;
            }
            
            .links-list a {
                color: #667eea;
                text-decoration: none;
                transition: color 0.3s ease;
            }
            
            .links-list a:hover {
                color: #764ba2;
            }
            
            @media (max-width: 1200px) {
                .settings-container {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </div>
    <?php
}
