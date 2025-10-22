<?php
/**
 * رفع مشکلات Elementor
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * بهبود سازگاری با Elementor
 */
function crypto_sekhyab_elementor_compatibility() {
    // اطمینان از لود شدن درست قالب
    add_theme_support('elementor');
    add_theme_support('elementor-page-builder');
    
    // پشتیبانی از ویژگی‌های پیشرفته
    add_theme_support('custom-header');
    add_theme_support('custom-background');
    add_theme_support('editor-styles');
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'crypto_sekhyab_elementor_compatibility');

/**
 * افزودن کلاس‌های سفارشی برای Elementor
 */
function crypto_sekhyab_elementor_body_class($classes) {
    if (defined('ELEMENTOR_VERSION')) {
        $classes[] = 'elementor-compatible';
    }
    
    return $classes;
}
add_filter('body_class', 'crypto_sekhyab_elementor_body_class');

/**
 * فعال‌سازی Canvas برای صفحات Elementor
 */
function crypto_sekhyab_elementor_canvas_support() {
    add_post_type_support('page', 'elementor');
    add_post_type_support('post', 'elementor');
    add_post_type_support('cryptocurrency', 'elementor');
}
add_action('init', 'crypto_sekhyab_elementor_canvas_support');

/**
 * کدهای اضافی فقط زمانی که Elementor نصب است
 */
if (defined('ELEMENTOR_VERSION')) {
    
    /**
     * ثبت مکان‌های Elementor
     */
    function crypto_sekhyab_register_elementor_locations($elementor_theme_manager) {
        $elementor_theme_manager->register_location('header');
        $elementor_theme_manager->register_location('footer');
        $elementor_theme_manager->register_location('single');
        $elementor_theme_manager->register_location('archive');
    }
    add_action('elementor/theme/register_locations', 'crypto_sekhyab_register_elementor_locations');
    
}
