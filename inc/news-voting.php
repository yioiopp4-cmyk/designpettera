<?php
/**
 * سیستم رای‌گیری برای اخبار (صعودی/نزولی)
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ثبت رای برای خبر
 */
function crypto_news_vote() {
    check_ajax_referer('crypto_vote_nonce', 'nonce');
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $vote_type = isset($_POST['vote_type']) ? sanitize_text_field($_POST['vote_type']) : '';
    
    if (!$post_id || !in_array($vote_type, array('bullish', 'bearish'))) {
        wp_send_json_error('Invalid data');
        return;
    }
    
    // بررسی اینکه آیا کاربر قبلاً رای داده
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $voted_key = 'voted_' . $post_id . '_' . md5($user_ip);
    
    // استفاده از transient برای جلوگیری از رای مکرر (24 ساعت)
    if (get_transient($voted_key)) {
        wp_send_json_error('Already voted');
        return;
    }
    
    // دریافت تعداد رای‌های فعلی
    $bullish_count = get_post_meta($post_id, '_bullish_votes', true);
    $bearish_count = get_post_meta($post_id, '_bearish_votes', true);
    
    $bullish_count = $bullish_count ? intval($bullish_count) : 0;
    $bearish_count = $bearish_count ? intval($bearish_count) : 0;
    
    // افزایش رای
    if ($vote_type === 'bullish') {
        $bullish_count++;
        update_post_meta($post_id, '_bullish_votes', $bullish_count);
    } else {
        $bearish_count++;
        update_post_meta($post_id, '_bearish_votes', $bearish_count);
    }
    
    // ذخیره اینکه کاربر رای داده
    set_transient($voted_key, true, 24 * HOUR_IN_SECONDS);
    
    wp_send_json_success(array(
        'bullish' => $bullish_count,
        'bearish' => $bearish_count
    ));
}
add_action('wp_ajax_crypto_news_vote', 'crypto_news_vote');
add_action('wp_ajax_nopriv_crypto_news_vote', 'crypto_news_vote');

/**
 * دریافت تعداد رای‌ها
 */
function crypto_get_vote_counts($post_id) {
    $bullish = get_post_meta($post_id, '_bullish_votes', true);
    $bearish = get_post_meta($post_id, '_bearish_votes', true);
    
    return array(
        'bullish' => $bullish ? intval($bullish) : 0,
        'bearish' => $bearish ? intval($bearish) : 0
    );
}

/**
 * بررسی اینکه آیا کاربر رای داده
 */
function crypto_has_user_voted($post_id) {
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $voted_key = 'voted_' . $post_id . '_' . md5($user_ip);
    return get_transient($voted_key) ? true : false;
}
