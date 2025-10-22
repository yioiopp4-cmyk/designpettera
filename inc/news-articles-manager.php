<?php
/**
 * مدیریت اخبار و مقالات
 * جدا کردن اخبار از مقالات و افزودن امکانات مدیریتی
 * 
 * @package CryptoSekhyab
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ایجاد taxonomy برای تفکیک اخبار و مقالات
 */
function crypto_sekhyab_register_content_type() {
    // ثبت taxonomy برای نوع محتوا
    register_taxonomy('content_type', 'post', array(
        'labels' => array(
            'name' => 'نوع محتوا',
            'singular_name' => 'نوع محتوا',
            'menu_name' => 'نوع محتوا',
            'all_items' => 'همه انواع',
            'edit_item' => 'ویرایش نوع',
            'update_item' => 'به‌روزرسانی نوع',
            'add_new_item' => 'افزودن نوع جدید',
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => false,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'content-type'),
    ));
}
add_action('init', 'crypto_sekhyab_register_content_type');

/**
 * ایجاد خودکار دسته‌های اخبار و مقالات
 */
function crypto_sekhyab_create_default_content_types() {
    // بررسی اینکه قبلاً اجرا شده یا نه
    if (get_option('crypto_sekhyab_content_types_created')) {
        return;
    }
    
    // ایجاد term برای اخبار
    if (!term_exists('اخبار', 'content_type')) {
        wp_insert_term('اخبار', 'content_type', array(
            'description' => 'اخبار فوری و به‌روزرسانی‌های بازار ارزهای دیجیتال',
            'slug' => 'news'
        ));
    }
    
    // ایجاد term برای مقالات
    if (!term_exists('مقالات', 'content_type')) {
        wp_insert_term('مقالات', 'content_type', array(
            'description' => 'مقالات آموزشی و تحلیلی در مورد ارزهای دیجیتال',
            'slug' => 'articles'
        ));
    }
    
    // علامت‌گذاری که اجرا شده
    update_option('crypto_sekhyab_content_types_created', true);
}
add_action('after_switch_theme', 'crypto_sekhyab_create_default_content_types');

/**
 * اضافه کردن Meta Box برای اخبار فوری
 */
function crypto_sekhyab_add_breaking_news_meta_box() {
    add_meta_box(
        'breaking_news_meta',
        'تنظیمات خبر فوری',
        'crypto_sekhyab_breaking_news_callback',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'crypto_sekhyab_add_breaking_news_meta_box');

/**
 * محتوای Meta Box اخبار فوری
 */
function crypto_sekhyab_breaking_news_callback($post) {
    wp_nonce_field('crypto_sekhyab_breaking_news_nonce', 'breaking_news_nonce');
    
    $is_breaking = get_post_meta($post->ID, 'breaking_news', true);
    ?>
    <p>
        <label>
            <input type="checkbox" name="is_breaking_news" value="1" <?php checked($is_breaking, '1'); ?>>
            <strong>این یک خبر فوری است</strong>
        </label>
    </p>
    <p class="description">اخبار فوری در نوار بالای صفحه اصلی نمایش داده می‌شوند.</p>
    <?php
}

/**
 * ذخیره Meta Data اخبار فوری
 */
function crypto_sekhyab_save_breaking_news_meta($post_id) {
    // بررسی nonce
    if (!isset($_POST['breaking_news_nonce']) || !wp_verify_nonce($_POST['breaking_news_nonce'], 'crypto_sekhyab_breaking_news_nonce')) {
        return;
    }
    
    // بررسی auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // بررسی دسترسی
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // بررسی اینکه آیا این یک پست است
    if (get_post_type($post_id) !== 'post') {
        return;
    }
    
    // ذخیره مقدار - استفاده از کلید یکسان 'breaking_news'
    if (isset($_POST['is_breaking_news']) && $_POST['is_breaking_news'] == '1') {
        update_post_meta($post_id, 'breaking_news', '1');
    } else {
        delete_post_meta($post_id, 'breaking_news');
    }
}
add_action('save_post', 'crypto_sekhyab_save_breaking_news_meta');

/**
 * اضافه کردن منوهای سریع در پنل ادمین
 */
function crypto_sekhyab_add_admin_menu_shortcuts() {
    // منوی اخبار
    add_menu_page(
        'مدیریت اخبار',
        'اخبار',
        'edit_posts',
        'edit.php?content_type=news',
        '',
        'dashicons-megaphone',
        25
    );
    
    // منوی مقالات
    add_menu_page(
        'مدیریت مقالات',
        'مقالات',
        'edit_posts',
        'edit.php?content_type=articles',
        '',
        'dashicons-book-alt',
        26
    );
}
add_action('admin_menu', 'crypto_sekhyab_add_admin_menu_shortcuts');

/**
 * فیلتر کردن پست‌ها بر اساس نوع محتوا
 */
function crypto_sekhyab_filter_posts_by_content_type($query) {
    global $pagenow;
    
    // فقط در صفحه لیست پست‌ها
    if ($pagenow == 'edit.php' && !isset($_GET['post_type'])) {
        if (isset($_GET['content_type'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'content_type',
                    'field' => 'slug',
                    'terms' => $_GET['content_type']
                )
            ));
        }
    }
}
add_filter('parse_query', 'crypto_sekhyab_filter_posts_by_content_type');

/**
 * اضافه کردن ستون نوع محتوا به لیست پست‌ها
 */
function crypto_sekhyab_add_content_type_column($columns) {
    $columns['content_type'] = 'نوع محتوا';
    $columns['is_breaking'] = 'خبر فوری';
    return $columns;
}
add_filter('manage_posts_columns', 'crypto_sekhyab_add_content_type_column');

/**
 * نمایش محتوای ستون نوع محتوا
 */
function crypto_sekhyab_show_content_type_column($column, $post_id) {
    if ($column == 'content_type') {
        $terms = get_the_terms($post_id, 'content_type');
        if ($terms && !is_wp_error($terms)) {
            $names = array();
            foreach ($terms as $term) {
                $names[] = $term->name;
            }
            echo implode(', ', $names);
        } else {
            echo '—';
        }
    }
    
    if ($column == 'is_breaking') {
        $is_breaking = get_post_meta($post_id, 'breaking_news', true);
        if ($is_breaking == '1') {
            echo '<span style="color: #d63638; font-weight: bold;">⚡ فوری</span>';
        } else {
            echo '—';
        }
    }
}
add_action('manage_posts_custom_column', 'crypto_sekhyab_show_content_type_column', 10, 2);
