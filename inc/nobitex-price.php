<?php
/**
 * نرخ خودکار تتر از نوبیتکس
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * دریافت نرخ تتر از نوبیتکس
 */
function get_usdt_price_from_nobitex() {
    $cache_key = 'nobitex_usdt_price';
    
    // چک Cache (2 دقیقه)
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return floatval($cached);
    }
    
    // درخواست به API نوبیتکس
    $response = wp_remote_get('https://api.nobitex.ir/v2/orderbook/USDTIRT', array(
        'timeout' => 10,
        'headers' => array('Accept' => 'application/json')
    ));
    
    if (is_wp_error($response)) {
        // در صورت خطا، نرخ پیش‌فرض
        return floatval(get_option('crypto_sekhyab_usdt_price', 114850));
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (isset($data['status']) && $data['status'] === 'ok') {
        // محاسبه میانگین بهترین قیمت خرید و فروش
        $asks = isset($data['asks']) ? $data['asks'] : array();
        $bids = isset($data['bids']) ? $data['bids'] : array();
        
        if (!empty($asks) && !empty($bids)) {
            $best_ask = floatval($asks[0][0]); // بهترین قیمت فروش
            $best_bid = floatval($bids[0][0]); // بهترین قیمت خرید
            
            // میانگین
            $usdt_price = ($best_ask + $best_bid) / 2;
            
            // ذخیره در Cache (2 دقیقه)
            set_transient($cache_key, $usdt_price, 120);
            
            // ذخیره به عنوان نرخ پیش‌فرض هم
            update_option('crypto_sekhyab_usdt_price', $usdt_price);
            
            return $usdt_price;
        }
    }
    
    // اگر مشکلی بود، نرخ قبلی
    return floatval(get_option('crypto_sekhyab_usdt_price', 114850));
}

/**
 * Helper function
 * توجه: تابع crypto_sekhyab_get_usdt_price() قبلاً در api-handler.php تعریف شده
 */

/**
 * AJAX برای دریافت نرخ
 */
function ajax_get_usdt_price() {
    $price = get_usdt_price_from_nobitex();
    wp_send_json_success(array(
        'price' => $price,
        'formatted' => number_format($price, 0),
        'source' => 'Nobitex'
    ));
}
add_action('wp_ajax_get_usdt_price', 'ajax_get_usdt_price');
add_action('wp_ajax_nopriv_get_usdt_price', 'ajax_get_usdt_price');

/**
 * Cron job برای بروزرسانی هر 5 دقیقه
 */
function schedule_usdt_price_update() {
    if (!wp_next_scheduled('update_usdt_price_cron')) {
        wp_schedule_event(time(), 'five_minutes', 'update_usdt_price_cron');
    }
}
add_action('wp', 'schedule_usdt_price_update');

function update_usdt_price_cron_handler() {
    get_usdt_price_from_nobitex();
}
add_action('update_usdt_price_cron', 'update_usdt_price_cron_handler');

/**
 * افزودن schedule 5 دقیقه‌ای
 */
function add_five_minutes_schedule($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display' => 'هر 5 دقیقه'
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_five_minutes_schedule');
