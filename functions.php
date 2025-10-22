<?php
/**
 * قالب کریپتو سخیاب - Functions
 *
 * @package CryptoSekhyab
 * @version 1.0.0
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// تعریف ثابت‌های قالب
define('CRYPTO_SEKHYAB_VERSION', '1.0.0');
define('CRYPTO_SEKHYAB_THEME_DIR', get_template_directory());
define('CRYPTO_SEKHYAB_THEME_URI', get_template_directory_uri());

/**
 * راه‌اندازی قالب
 */
function crypto_sekhyab_setup() {
    // پشتیبانی از ترجمه
    load_theme_textdomain('crypto-sekhyab', CRYPTO_SEKHYAB_THEME_DIR . '/languages');

    // پشتیبانی از عنوان خودکار
    add_theme_support('title-tag');

    // پشتیبانی از تصویر شاخص
    add_theme_support('post-thumbnails');
    
    // اندازه‌های تصویر سفارشی
    add_image_size('crypto-thumbnail', 400, 250, true);
    add_image_size('crypto-large', 800, 500, true);
    add_image_size('news-card', 400, 300, true);

    // پشتیبانی از HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // پشتیبانی از لوگوی سفارشی
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // ثبت منوها
    register_nav_menus(array(
        'primary' => __('منوی اصلی', 'crypto-sekhyab'),
        'footer'  => __('منوی فوتر', 'crypto-sekhyab'),
    ));

    // پشتیبانی از Elementor
    add_theme_support('elementor');
}
add_action('after_setup_theme', 'crypto_sekhyab_setup');

/**
 * ثبت Custom Post Type برای ارزها
 */
function crypto_sekhyab_register_post_types() {
    $labels = array(
        'name'               => 'ارزهای دیجیتال',
        'singular_name'      => 'ارز دیجیتال',
        'menu_name'          => 'ارزهای دیجیتال',
        'add_new'            => 'افزودن ارز جدید',
        'add_new_item'       => 'افزودن ارز دیجیتال جدید',
        'edit_item'          => 'ویرایش ارز دیجیتال',
        'new_item'           => 'ارز دیجیتال جدید',
        'view_item'          => 'مشاهده ارز دیجیتال',
        'search_items'       => 'جستجوی ارز دیجیتال',
        'not_found'          => 'ارزی یافت نشد',
        'not_found_in_trash' => 'ارزی در زباله‌دان یافت نشد',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-chart-line',
        'supports'            => array('title', 'editor', 'thumbnail', 'comments'),
        'rewrite'             => array('slug' => 'crypto'),
        'capability_type'     => 'post',
    );

    register_post_type('cryptocurrency', $args);

    // ثبت تکسانومی برای دسته‌بندی ارزها
    register_taxonomy('crypto_category', 'cryptocurrency', array(
        'labels' => array(
            'name'          => 'دسته‌بندی ارزها',
            'singular_name' => 'دسته‌بندی',
        ),
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'crypto-category'),
    ));
}
add_action('init', 'crypto_sekhyab_register_post_types');

/**
 * اضافه کردن Meta Boxes برای ارزها
 */
function crypto_sekhyab_add_meta_boxes() {
    add_meta_box(
        'crypto_details',
        'اطلاعات ارز دیجیتال',
        'crypto_sekhyab_crypto_details_callback',
        'cryptocurrency',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'crypto_sekhyab_add_meta_boxes');

/**
 * نمایش محتوای Meta Box
 */
function crypto_sekhyab_crypto_details_callback($post) {
    wp_nonce_field('crypto_sekhyab_save_meta', 'crypto_sekhyab_meta_nonce');
    
    $symbol = get_post_meta($post->ID, '_crypto_symbol', true);
    $coingecko_id = get_post_meta($post->ID, '_crypto_coingecko_id', true);
    $tradingview_symbol = get_post_meta($post->ID, '_crypto_tradingview_symbol', true);
    $news_cat = get_post_meta($post->ID, '_crypto_news_category', true);
    $news_tag = get_post_meta($post->ID, '_crypto_news_tag', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="crypto_symbol">نماد ارز (Symbol)</label></th>
            <td>
                <input type="text" id="crypto_symbol" name="crypto_symbol" value="<?php echo esc_attr($symbol); ?>" class="regular-text" placeholder="BTC">
                <p class="description">مثال: BTC, ETH, BNB</p>
            </td>
        </tr>
        <tr>
            <th><label for="crypto_coingecko_id">شناسه CoinGecko</label></th>
            <td>
                <input type="text" id="crypto_coingecko_id" name="crypto_coingecko_id" value="<?php echo esc_attr($coingecko_id); ?>" class="regular-text" placeholder="bitcoin">
                <p class="description">برای دریافت اطلاعات قیمت از CoinGecko API</p>
            </td>
        </tr>
        <tr>
            <th><label for="crypto_tradingview_symbol">نماد TradingView</label></th>
            <td>
                <input type="text" id="crypto_tradingview_symbol" name="crypto_tradingview_symbol" value="<?php echo esc_attr($tradingview_symbol); ?>" class="regular-text" placeholder="BINANCE:BTCUSDT">
                <p class="description">مثال: BINANCE:BTCUSDT</p>
            </td>
        </tr>
    </table>
    <h3>اخبار مرتبط</h3>
    <table class="form-table">
        <tr>
            <th><label for="crypto_news_category">دسته‌بندی اخبار (Slug)</label></th>
            <td>
                <input type="text" id="crypto_news_category" name="crypto_news_category" value="<?php echo esc_attr($news_cat); ?>" class="regular-text" placeholder="bitcoin-news">
                <p class="description">اسلاگ دسته اخبار مرتبط با این ارز (اختیاری)</p>
            </td>
        </tr>
        <tr>
            <th><label for="crypto_news_tag">برچسب اخبار (Slug)</label></th>
            <td>
                <input type="text" id="crypto_news_tag" name="crypto_news_tag" value="<?php echo esc_attr($news_tag); ?>" class="regular-text" placeholder="btc">
                <p class="description">اسلاگ برچسب اخبار مرتبط با این ارز (اختیاری)</p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * ذخیره Meta Data
 */
function crypto_sekhyab_save_meta($post_id) {
    if (!isset($_POST['crypto_sekhyab_meta_nonce']) || !wp_verify_nonce($_POST['crypto_sekhyab_meta_nonce'], 'crypto_sekhyab_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['crypto_symbol'])) {
        update_post_meta($post_id, '_crypto_symbol', sanitize_text_field($_POST['crypto_symbol']));
    }

    if (isset($_POST['crypto_coingecko_id'])) {
        update_post_meta($post_id, '_crypto_coingecko_id', sanitize_text_field($_POST['crypto_coingecko_id']));
    }

    if (isset($_POST['crypto_tradingview_symbol'])) {
        update_post_meta($post_id, '_crypto_tradingview_symbol', sanitize_text_field($_POST['crypto_tradingview_symbol']));
    }
    if (isset($_POST['crypto_news_category'])) {
        update_post_meta($post_id, '_crypto_news_category', sanitize_text_field($_POST['crypto_news_category']));
    }
    if (isset($_POST['crypto_news_tag'])) {
        update_post_meta($post_id, '_crypto_news_tag', sanitize_text_field($_POST['crypto_news_tag']));
    }
}

function crypto_sekhyab_enqueue_scripts() {
    // استایل اصلی
    wp_enqueue_style('crypto-sekhyab-style', get_stylesheet_uri(), array(), CRYPTO_SEKHYAB_VERSION);

    // استایل‌های اضافی
    wp_enqueue_style('crypto-sekhyab-main', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/main.css', array(), CRYPTO_SEKHYAB_VERSION);
    wp_enqueue_style('crypto-sekhyab-modern', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/modern-style.css', array(), CRYPTO_SEKHYAB_VERSION);
    wp_enqueue_style('crypto-sekhyab-enhanced', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/enhanced-style.css', array(), CRYPTO_SEKHYAB_VERSION);
    wp_enqueue_style('crypto-sekhyab-arzdigital', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/arzdigital-style.css', array(), CRYPTO_SEKHYAB_VERSION);

    // استایل و اسکریپت صفحه اخبار
    if (is_page_template('page-news.php')) {
        wp_enqueue_style('news-archive-style', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/news-archive.css', array(), CRYPTO_SEKHYAB_VERSION);
        wp_enqueue_script('news-archive-script', CRYPTO_SEKHYAB_THEME_URI . '/assets/js/news-archive.js', array('jquery'), CRYPTO_SEKHYAB_VERSION, true);
    }

    // استایل‌های کتابخانه‌ها و افزونه‌ها
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0');
    wp_enqueue_style('crypto-sekhyab-coingecko', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/coingecko-style.css', array(), CRYPTO_SEKHYAB_VERSION);
    wp_enqueue_style('crypto-sekhyab-advanced-ui', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/advanced-ui.css', array(), CRYPTO_SEKHYAB_VERSION);
    wp_enqueue_style('crypto-sekhyab-beautiful-ui', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/beautiful-ui.css', array(), CRYPTO_SEKHYAB_VERSION);

    // استایل سفارشی برای همسان‌سازی با تصاویر TF (در انتها برای override) + کش بسـت (با زمان آخرین تغییر)
    $tf_path = CRYPTO_SEKHYAB_THEME_DIR . '/assets/css/tf-overrides.css';
    $tf_ver  = file_exists($tf_path) ? filemtime($tf_path) : CRYPTO_SEKHYAB_VERSION;
    wp_enqueue_style('crypto-sekhyab-tf-overrides', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/tf-overrides.css', array(), $tf_ver);
    
    // Final Fixes CSS
    wp_enqueue_style('crypto-sekhyab-final-fixes', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/final-fixes.css', array(), time());
    
    // Modern Scrollbar CSS
    wp_enqueue_style('crypto-sekhyab-scrollbar', CRYPTO_SEKHYAB_THEME_URI . '/assets/css/scrollbar-modern.css', array(), time());

    // اسکریپت‌ها
    wp_enqueue_script('crypto-sekhyab-main', CRYPTO_SEKHYAB_THEME_URI . '/assets/js/main.js', array('jquery'), CRYPTO_SEKHYAB_VERSION, true);
    
    // News Slider JS با نسخه جدید
    wp_enqueue_script('crypto-sekhyab-news-slider', CRYPTO_SEKHYAB_THEME_URI . '/assets/js/news-slider.js', array(), time(), true);
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true);
    wp_enqueue_script('tradingview-widget', 'https://s3.tradingview.com/tv.js', array(), null, true);

    // داده‌های PHP به JavaScript
    wp_localize_script('crypto-sekhyab-main', 'cryptoSekhyabData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('crypto_sekhyab_nonce'),
        'usdToIrr' => get_option('crypto_sekhyab_usd_to_irr', 50000),
    ));
}
add_action('wp_enqueue_scripts', 'crypto_sekhyab_enqueue_scripts');
// ذخیره متادیتا هنگام ذخیره پست ارز دیجیتال
add_action('save_post_cryptocurrency', 'crypto_sekhyab_save_meta');

/**
 * ثبت Sidebar
 */
function crypto_sekhyab_widgets_init() {
    register_sidebar(array(
        'name'          => __('سایدبار اصلی', 'crypto-sekhyab'),
        'id'            => 'sidebar-1',
        'description'   => __('سایدبار اصلی سایت', 'crypto-sekhyab'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('فوتر 1', 'crypto-sekhyab'),
        'id'            => 'footer-1',
        'description'   => __('ستون اول فوتر', 'crypto-sekhyab'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('فوتر 2', 'crypto-sekhyab'),
        'id'            => 'footer-2',
        'description'   => __('ستون دوم فوتر', 'crypto-sekhyab'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('فوتر 3', 'crypto-sekhyab'),
        'id'            => 'footer-3',
        'description'   => __('ستون سوم فوتر', 'crypto-sekhyab'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'crypto_sekhyab_widgets_init');

/**
 * AJAX: دریافت قیمت ارزها از CoinGecko
 */
function crypto_sekhyab_get_crypto_prices() {
    check_ajax_referer('crypto_sekhyab_nonce', 'nonce');
    
    $crypto_ids = isset($_POST['crypto_ids']) ? sanitize_text_field($_POST['crypto_ids']) : '';
    
    if (empty($crypto_ids)) {
        wp_send_json_error('No crypto IDs provided');
        return;
    }
    
    // بررسی Cache
    $cache_key = 'crypto_prices_' . md5($crypto_ids);
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        wp_send_json_success($cached_data);
        return;
    }
    
    // درخواست به API
    $api_url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . $crypto_ids . '&vs_currencies=usd&include_24hr_change=true&include_market_cap=true';
    
    $response = wp_remote_get($api_url, array('timeout' => 15));
    
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    // ذخیره در Cache برای 2 دقیقه
    set_transient($cache_key, $data, 120);
    
    wp_send_json_success($data);
}
add_action('wp_ajax_get_crypto_prices', 'crypto_sekhyab_get_crypto_prices');
add_action('wp_ajax_nopriv_get_crypto_prices', 'crypto_sekhyab_get_crypto_prices');

/**
 * شامل کردن فایل‌های اضافی
 */
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/theme-options.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/crypto-functions.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/logger.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/api-handler.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/logs-page.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/auto-crypto-creator.php';
// متا باکس اخبار فوری حذف شد - اکنون در inc/news-articles-manager.php مدیریت می‌شود
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/performance-optimizer.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/elementor-fix.php';

/**
 * بارگذاری ویجت‌های Elementor
 */
function crypto_sekhyab_load_elementor_widgets() {
    // فقط اگر Elementor نصب و فعال باشد
    if (did_action('elementor/loaded')) {
        require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/elementor-widgets.php';
        crypto_sekhyab_log('INFO', 'Elementor widgets loaded');
    } else {
        crypto_sekhyab_log('DEBUG', 'Elementor not loaded - widgets skipped');
    }
}
add_action('plugins_loaded', 'crypto_sekhyab_load_elementor_widgets');

/**
 * اضافه کردن کلاس body سفارشی
 */
function crypto_sekhyab_body_classes($classes) {
    if (is_singular('cryptocurrency')) {
        $classes[] = 'single-crypto-page';
    }
    
    if (is_rtl()) {
        $classes[] = 'rtl';
    }
    
    // برچسب برای استایل صفحه اصلی (جهت اعمال TF حتی اگر قالب صفحه دیگری رندر شود)
    if (is_front_page()) {
        $classes[] = 'tf-home';
    }
    
    return $classes;
}
add_filter('body_class', 'crypto_sekhyab_body_classes');

/**
 * ایجاد خودکار صفحات پیش‌فرض
 */
function crypto_sekhyab_create_default_pages() {
    crypto_sekhyab_log('INFO', 'Running default pages creation');
    // بررسی اینکه قبلاً اجرا شده یا نه
    if (get_option('crypto_sekhyab_pages_created')) {
        return;
    }
    
    // صفحه لیست ارزها
    $crypto_list_page = array(
        'post_title'    => 'لیست ارزها',
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'crypto-list',
        'page_template' => 'page-crypto-list.php'
    );
    
    $page_id = wp_insert_post($crypto_list_page);
    
    if ($page_id) {
        update_post_meta($page_id, '_wp_page_template', 'page-crypto-list.php');
    }
    
    // علامت‌گذاری به عنوان اجرا شده
    update_option('crypto_sekhyab_pages_created', true);
}
add_action('after_switch_theme', 'crypto_sekhyab_create_default_pages');

/**
 * دستورات مدیریت لاگ: پاک‌سازی و دانلود
 */
function crypto_sekhyab_handle_clear_logs() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    check_admin_referer('crypto_sekhyab_logs_nonce');
    crypto_sekhyab_clear_logs();
    crypto_sekhyab_log('WARNING', 'Logs cleared by admin', array('user' => get_current_user_id()));
    wp_safe_redirect(admin_url('admin.php?page=crypto-sekhyab-logs&cleared=1'));
    exit;
}
add_action('admin_post_crypto_sekhyab_clear_logs', 'crypto_sekhyab_handle_clear_logs');

function crypto_sekhyab_handle_download_logs() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    check_admin_referer('crypto_sekhyab_logs_nonce');
    $path = crypto_sekhyab_get_log_file_path();
    if (!file_exists($path)) {
        wp_die('No log file');
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="crypto-sekhyab.log"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}
add_action('admin_post_crypto_sekhyab_download_logs', 'crypto_sekhyab_handle_download_logs');

/**
 * اعلان نصب Elementor (اختیاری)
 */
function crypto_sekhyab_elementor_notice() {
    if (!did_action('elementor/loaded') && current_user_can('install_plugins')) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong>قالب کریپتو سخیاب:</strong> 
                برای استفاده از ویجت‌های سفارشی، لطفاً افزونه 
                <a href="<?php echo admin_url('plugin-install.php?s=elementor&tab=search&type=term'); ?>" target="_blank">Elementor</a> 
                را نصب کنید. (اختیاری - قالب بدون آن هم کار می‌کند)
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'crypto_sekhyab_elementor_notice');

// شامل کردن فایل‌های بهینه‌ساز و ابزارهای جدید
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/auto-crypto-creator.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/performance-optimizer.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/elementor-fix.php';

// سیستم CoinGecko
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/coingecko-api-handler.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/batch-creator-admin.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/theme-customizer.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/cache-cleaner.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/bulk-delete-cryptos.php';
require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/news-voting.php';

// سیستم CoinMarketCap (غیرفعال - فقط از CoinGecko استفاده می‌شود)
// require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/coinmarketcap-api.php';
// require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/cmc-settings.php';
// require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/cmc-batch-creator.php';

// نرخ خودکار تتر از نوبیتکس
// توجه: تابع crypto_sekhyab_get_usdt_price() در inc/api-handler.php موجود است
// require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/nobitex-price.php';

/**
 * AJAX: دریافت قیمت لحظه‌ای ارز
 */
function ajax_get_crypto_live_price() {
    $api_source = isset($_POST['api_source']) ? sanitize_text_field($_POST['api_source']) : 'coinmarketcap';
    $usdt_rate = crypto_sekhyab_get_usdt_price();
    
    $data = array();
    
    if ($api_source == 'coinmarketcap') {
        $cmc_id = isset($_POST['cmc_id']) ? intval($_POST['cmc_id']) : 0;
        if ($cmc_id) {
            $cmc = cmc_api();
            $quotes = $cmc->get_quotes($cmc_id);
            if ($quotes && isset($quotes[$cmc_id])) {
                $coin = $quotes[$cmc_id];
                $data = array(
                    'price_usd' => $coin['quote']['USD']['price'] ?? 0,
                    'price_irr' => ($coin['quote']['USD']['price'] ?? 0) * $usdt_rate,
                    'change_1h' => $coin['quote']['USD']['percent_change_1h'] ?? 0,
                    'change_24h' => $coin['quote']['USD']['percent_change_24h'] ?? 0,
                    'change_7d' => $coin['quote']['USD']['percent_change_7d'] ?? 0,
                    'market_cap' => $coin['quote']['USD']['market_cap'] ?? 0,
                    'volume_24h' => $coin['quote']['USD']['volume_24h'] ?? 0,
                );
            }
        }
    } else {
        // CoinGecko
        $coingecko_id = isset($_POST['coingecko_id']) ? sanitize_text_field($_POST['coingecko_id']) : '';
        if ($coingecko_id) {
            $api = cg_api();
            $coin = $api->get_coin_details($coingecko_id);
            if ($coin && is_array($coin)) {
                $data = array(
                    'price_usd' => $coin['market_data']['current_price']['usd'] ?? 0,
                    'price_irr' => ($coin['market_data']['current_price']['usd'] ?? 0) * $usdt_rate,
                    'change_1h' => $coin['market_data']['price_change_percentage_1h_in_currency']['usd'] ?? 0,
                    'change_24h' => $coin['market_data']['price_change_percentage_24h'] ?? 0,
                    'change_7d' => $coin['market_data']['price_change_percentage_7d'] ?? 0,
                    'market_cap' => $coin['market_data']['market_cap']['usd'] ?? 0,
                    'volume_24h' => $coin['market_data']['total_volume']['usd'] ?? 0,
                );
            }
        }
    }
    
    if (!empty($data)) {
        wp_send_json_success($data);
    } else {
        wp_send_json_error('داده‌ای یافت نشد');
    }
}
add_action('wp_ajax_get_crypto_live_price', 'ajax_get_crypto_live_price');
add_action('wp_ajax_nopriv_get_crypto_live_price', 'ajax_get_crypto_live_price');

function cryptosekhyab_single_post_styles() {
    if (is_singular('post')) { // ✅ فقط برای پست‌های معمولی
        wp_enqueue_style(
            'single-post-style',
            get_template_directory_uri() . '/assets/css/single-post.css',
            array(),
            filemtime(get_template_directory() . '/assets/css/single-post.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'cryptosekhyab_single_post_styles');

// ثبت بازدید پست
function record_post_view_ajax() {
    check_ajax_referer('crypto_nonce', 'nonce');
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if ($post_id > 0) {
        $count = get_post_meta($post_id, 'post_views_count', true);
        $count = $count ? $count : 0;
        update_post_meta($post_id, 'post_views_count', $count + 1);
        
        wp_send_json_success(array('views' => $count + 1));
    }
    
    wp_send_json_error('Invalid post ID');
}
add_action('wp_ajax_record_post_view', 'record_post_view_ajax');
add_action('wp_ajax_nopriv_record_post_view', 'record_post_view_ajax');

// شمارش لایک پست
function like_post_ajax() {
    check_ajax_referer('crypto_nonce', 'nonce');
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    if ($post_id > 0) {
        $count = get_post_meta($post_id, 'post_likes_count', true);
        $count = $count ? $count : 0;
        update_post_meta($post_id, 'post_likes_count', $count + 1);
        
        wp_send_json_success(array('likes' => $count + 1));
    }
    
    wp_send_json_error('Invalid post ID');
}
add_action('wp_ajax_like_post', 'like_post_ajax');
add_action('wp_ajax_nopriv_like_post', 'like_post_ajax');

// لود مدیریت اخبار و مقالات
if (file_exists(CRYPTO_SEKHYAB_THEME_DIR . '/inc/news-articles-manager.php')) {
    require_once CRYPTO_SEKHYAB_THEME_DIR . '/inc/news-articles-manager.php';
}

/**
 * AJAX: دریافت داده‌های نمودار بازار
 */
function ajax_get_market_chart() {
    check_ajax_referer('crypto_sekhyab_nonce', 'nonce');
    
    $coin_id = isset($_POST['coin_id']) ? sanitize_text_field($_POST['coin_id']) : '';
    $days = isset($_POST['days']) ? intval($_POST['days']) : 7;
    
    if (empty($coin_id)) {
        wp_send_json_error('Invalid coin ID');
        return;
    }
    
    // کش برای 5 دقیقه
    $cache_key = 'market_chart_' . $coin_id . '_' . $days;
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        wp_send_json_success($cached);
        return;
    }
    
    // درخواست به CoinGecko API
    $url = 'https://api.coingecko.com/api/v3/coins/' . $coin_id . '/market_chart';
    $response = wp_remote_get(add_query_arg(array(
        'vs_currency' => 'usd',
        'days' => $days
    ), $url), array('timeout' => 15));
    
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!empty($data)) {
        set_transient($cache_key, $data, 300); // 5 دقیقه
        wp_send_json_success($data);
    } else {
        wp_send_json_error('No data received');
    }
}
add_action('wp_ajax_get_market_chart', 'ajax_get_market_chart');
add_action('wp_ajax_nopriv_get_market_chart', 'ajax_get_market_chart');

