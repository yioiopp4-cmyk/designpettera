<?php
/**
 * بهینه‌سازی سرعت سایت
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * فعال‌سازی کش بهینه
 */
function crypto_sekhyab_optimize_cache() {
    $enable_cache = get_option('crypto_sekhyab_enable_cache', '1');
    
    if ($enable_cache != '1') {
        return;
    }
    
    // افزایش زمان کش برای بهبود سرعت
    add_filter('wp_cron_schedules', 'crypto_sekhyab_add_cron_interval');
}
add_action('init', 'crypto_sekhyab_optimize_cache');

/**
 * اضافه کردن interval سفارشی برای Cron
 */
function crypto_sekhyab_add_cron_interval($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display' => __('هر 5 دقیقه')
    );
    
    $schedules['fifteen_minutes'] = array(
        'interval' => 900,
        'display' => __('هر 15 دقیقه')
    );
    
    return $schedules;
}

/**
 * حذف query strings از static resources
 */
function crypto_sekhyab_remove_query_strings($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'crypto_sekhyab_remove_query_strings', 10, 2);
add_filter('script_loader_src', 'crypto_sekhyab_remove_query_strings', 10, 2);

/**
 * اضافه کردن Lazy Loading برای تصاویر
 */
function crypto_sekhyab_add_lazy_loading($attr) {
    $attr['loading'] = 'lazy';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'crypto_sekhyab_add_lazy_loading');

/**
 * بهینه‌سازی دیتابیس queries
 */
function crypto_sekhyab_optimize_queries() {
    // محدود کردن revisions
    if (!defined('WP_POST_REVISIONS')) {
        define('WP_POST_REVISIONS', 3);
    }
    
    // افزایش memory limit
    if (!defined('WP_MEMORY_LIMIT')) {
        define('WP_MEMORY_LIMIT', '256M');
    }
}
add_action('init', 'crypto_sekhyab_optimize_queries');

/**
 * Defer JavaScript Loading
 */
function crypto_sekhyab_defer_scripts($tag, $handle) {
    // اسکریپت‌هایی که نباید defer شوند
    $exclude = array('jquery', 'jquery-core', 'jquery-migrate', 'tradingview-widget');
    
    if (in_array($handle, $exclude)) {
        return $tag;
    }
    
    // اضافه کردن defer
    // only add defer for front-end
    if (!is_admin()) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'crypto_sekhyab_defer_scripts', 10, 2);

/**
 * پاک‌سازی خودکار Transients منقضی شده
 */
function crypto_sekhyab_cleanup_transients() {
    global $wpdb;
    
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_crypto_%' AND option_value < " . time());
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_crypto_%' AND option_name NOT IN (SELECT DISTINCT REPLACE(option_name, '_timeout', '') FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_crypto_%')");
}

// اجرای هفتگی
if (!wp_next_scheduled('crypto_sekhyab_cleanup_transients')) {
    wp_schedule_event(time(), 'weekly', 'crypto_sekhyab_cleanup_transients');
}
add_action('crypto_sekhyab_cleanup_transients', 'crypto_sekhyab_cleanup_transients');

/**
 * Minify HTML Output
 */
function crypto_sekhyab_minify_html($html) {
    if (!get_option('crypto_sekhyab_enable_cache', '1')) {
        return $html;
    }
    
    // حذف فضاهای خالی اضافی
    $html = preg_replace('/\s+/', ' ', $html);
    $html = preg_replace('/<!--(?!<!)[^\[>].*?-->/', '', $html);
    
    return $html;
}

/**
 * Browser Caching Headers
 */
function crypto_sekhyab_browser_cache() {
    if (!is_admin()) {
        header('Cache-Control: public, max-age=31536000');
    }
}
add_action('send_headers', 'crypto_sekhyab_browser_cache');
