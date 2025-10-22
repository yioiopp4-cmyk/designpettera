<?php
/**
 * Arzdigital Style Loader
 * لود کردن استایل Arzdigital بدون تغییر در functions.php
 * 
 * @package CryptoSekhyab
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * لود کردن استایل Arzdigital
 */
function arzdigital_load_styles() {
    // فقط در صفحه اصلی
    if (!is_front_page()) {
        return;
    }
    
    // لود استایل کامل Arzdigital
    $arzdigital_path = CRYPTO_SEKHYAB_THEME_DIR . '/assets/css/arzdigital-complete.css';
    $arzdigital_ver  = file_exists($arzdigital_path) ? filemtime($arzdigital_path) : CRYPTO_SEKHYAB_VERSION;
    
    wp_enqueue_style(
        'crypto-sekhyab-arzdigital-complete', 
        CRYPTO_SEKHYAB_THEME_URI . '/assets/css/arzdigital-complete.css', 
        array(), 
        $arzdigital_ver
    );
    
    // لود فایل fixes
    $fixes_path = CRYPTO_SEKHYAB_THEME_DIR . '/assets/css/arzdigital-fixes.css';
    $fixes_ver  = file_exists($fixes_path) ? filemtime($fixes_path) : CRYPTO_SEKHYAB_VERSION;
    
    wp_enqueue_style(
        'crypto-sekhyab-arzdigital-fixes', 
        CRYPTO_SEKHYAB_THEME_URI . '/assets/css/arzdigital-fixes.css', 
        array('crypto-sekhyab-arzdigital-complete'), 
        $fixes_ver
    );
    
    // لود header fixes
    $header_fixes_path = CRYPTO_SEKHYAB_THEME_DIR . '/assets/css/header-fixes.css';
    $header_fixes_ver  = file_exists($header_fixes_path) ? filemtime($header_fixes_path) : CRYPTO_SEKHYAB_VERSION;
    
    wp_enqueue_style(
        'crypto-sekhyab-header-fixes', 
        CRYPTO_SEKHYAB_THEME_URI . '/assets/css/header-fixes.css', 
        array('crypto-sekhyab-arzdigital-fixes'), 
        $header_fixes_ver
    );
    
    // لود force fixes با بالاترین اولویت
    $force_fixes_path = CRYPTO_SEKHYAB_THEME_DIR . '/assets/css/force-fixes.css';
    $force_fixes_ver  = file_exists($force_fixes_path) ? filemtime($force_fixes_path) : CRYPTO_SEKHYAB_VERSION;
    
    wp_enqueue_style(
        'crypto-sekhyab-force-fixes', 
        CRYPTO_SEKHYAB_THEME_URI . '/assets/css/force-fixes.css', 
        array('crypto-sekhyab-header-fixes'), 
        $force_fixes_ver
    );
}
add_action('wp_enqueue_scripts', 'arzdigital_load_styles', 999); // اولویت بالا برای override

/**
 * اضافه کردن کلاس arzdigital-style به body
 */
function arzdigital_body_class($classes) {
    if (is_front_page()) {
        $classes[] = 'arzdigital-style';
    }
    return $classes;
}
add_filter('body_class', 'arzdigital_body_class', 999);
