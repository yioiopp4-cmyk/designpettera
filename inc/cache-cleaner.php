<?php
/**
 * ابزار پاک‌سازی Cache
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * افزودن منو
 */
function cg_add_cache_cleaner_menu() {
    add_submenu_page(
        'crypto-sekhyab-options',
        'پاک‌سازی Cache',
        'پاک‌سازی Cache',
        'manage_options',
        'cg-cache-cleaner',
        'cg_cache_cleaner_page'
    );
}
add_action('admin_menu', 'cg_add_cache_cleaner_menu');

/**
 * صفحه پاک‌سازی
 */
function cg_cache_cleaner_page() {
    $message = '';
    
    if (isset($_POST['clear_cache']) && check_admin_referer('cg_clear_cache', 'cg_cache_nonce')) {
        global $wpdb;
        
        // پاک کردن transients
        $deleted1 = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_cg_%' OR option_name LIKE '_transient_crypto_%'");
        $deleted2 = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_cg_%' OR option_name LIKE '_transient_timeout_crypto_%'");
        
        // پاک کردن فایل‌های Cache
        $cache_dir = WP_CONTENT_DIR . '/uploads/cg-cache';
        $deleted_files = 0;
        
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*.json');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $deleted_files++;
                }
            }
        }
        
        // بهینه‌سازی جدول
        $wpdb->query("OPTIMIZE TABLE $wpdb->options");
        
        $message = '<div class="notice notice-success"><p><strong>✅ Cache با موفقیت پاک شد!</strong><br>';
        $message .= "Transients پاک شده: " . ($deleted1 + $deleted2) . "<br>";
        $message .= "فایل‌های Cache پاک شده: " . $deleted_files . "<br>";
        $message .= "جدول بهینه شد</p></div>";
    }
    
    // شمارش cache های موجود
    global $wpdb;
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE '_transient_cg_%' OR option_name LIKE '_transient_crypto_%'");
    
    // شمارش فایل‌های Cache
    $cache_dir = WP_CONTENT_DIR . '/uploads/cg-cache';
    $file_count = 0;
    $file_size = 0;
    
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '/*.json');
        $file_count = count($files);
        foreach ($files as $file) {
            if (is_file($file)) {
                $file_size += filesize($file);
            }
        }
    }
    
    ?>
    <div class="wrap">
        <h1>🧹 پاک‌سازی Cache</h1>
        
        <?php echo $message; ?>
        
        <div class="card" style="max-width: 800px; padding: 24px; margin-top: 24px;">
            <h2>وضعیت Cache</h2>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 13px; color: #64748b; margin-bottom: 8px;">Cache های Database</div>
                    <div style="font-size: 32px; font-weight: 800; color: #0d1421;">
                        <?php echo number_format($count); ?>
                    </div>
                </div>
                
                <div style="background: #f0fdf4; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 13px; color: #16a34a; margin-bottom: 8px;">فایل‌های Cache</div>
                    <div style="font-size: 32px; font-weight: 800; color: #16a34a;">
                        <?php echo number_format($file_count); ?>
                    </div>
                </div>
                
                <div style="background: #fef2f2; padding: 20px; border-radius: 12px;">
                    <div style="font-size: 13px; color: #dc2626; margin-bottom: 8px;">حجم فایل‌ها</div>
                    <div style="font-size: 32px; font-weight: 800; color: #dc2626;">
                        <?php 
                        if ($file_size > 1024 * 1024) {
                            echo number_format($file_size / 1024 / 1024, 2) . ' MB';
                        } elseif ($file_size > 1024) {
                            echo number_format($file_size / 1024, 2) . ' KB';
                        } else {
                            echo $file_size . ' B';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div style="background: #dbeafe; border-right: 4px solid #3b82f6; padding: 16px; border-radius: 8px; margin: 20px 0;">
                <strong>💡 سیستم جدید File-based Caching:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    <li>داده‌های بزرگ (مثل لیست 19,000 ارز) در فایل ذخیره می‌شوند</li>
                    <li>بدون محدودیت max_allowed_packet</li>
                    <li>سریع‌تر از Database</li>
                    <li>کمتر فشار روی MySQL</li>
                </ul>
            </div>
            
            <h3>کاربرد Cache:</h3>
            <ul style="line-height: 2;">
                <li>✅ افزایش سرعت سایت</li>
                <li>✅ کاهش درخواست به API</li>
                <li>✅ بهبود تجربه کاربری</li>
                <li>✅ بدون محدودیت حجم</li>
            </ul>
            
            <h3>چه زمانی باید Cache را پاک کنید:</h3>
            <ul style="line-height: 2;">
                <li>🔴 وقتی خطای Database دارید</li>
                <li>🔴 وقتی قیمت‌ها بروز نمی‌شوند</li>
                <li>🔴 وقتی تنظیمات اعمال نمی‌شوند</li>
                <li>🔴 بعد از بروزرسانی قالب</li>
            </ul>
            
            <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 16px; border-radius: 8px; margin: 20px 0;">
                <strong>⚠️ توجه:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    <li>بعد از پاک کردن، سایت کمی کند می‌شود (تا Cache دوباره ساخته شود)</li>
                    <li>این کار کاملاً ایمن است</li>
                    <li>Cache به صورت خودکار دوباره ساخته می‌شود</li>
                </ul>
            </div>
            
            <form method="post">
                <?php wp_nonce_field('cg_clear_cache', 'cg_cache_nonce'); ?>
                <p>
                    <button type="submit" name="clear_cache" class="button button-primary button-hero">
                        🧹 پاک کردن همه Cache ها
                    </button>
                </p>
            </form>
        </div>
        
        <div class="card" style="max-width: 800px; padding: 24px; margin-top: 24px;">
            <h2>📊 جزئیات Cache</h2>
            
            <?php
            $caches = $wpdb->get_results("
                SELECT option_name, LENGTH(option_value) as size 
                FROM $wpdb->options 
                WHERE option_name LIKE '_transient_cg_%' OR option_name LIKE '_transient_crypto_%'
                ORDER BY size DESC
                LIMIT 20
            ");
            
            if ($caches) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>نام Cache</th><th>حجم</th></tr></thead>';
                echo '<tbody>';
                foreach ($caches as $cache) {
                    $name = str_replace('_transient_', '', $cache->option_name);
                    $size = $cache->size;
                    
                    if ($size > 1024 * 1024) {
                        $size_str = number_format($size / 1024 / 1024, 2) . ' MB';
                    } elseif ($size > 1024) {
                        $size_str = number_format($size / 1024, 2) . ' KB';
                    } else {
                        $size_str = $size . ' B';
                    }
                    
                    echo '<tr>';
                    echo '<td><code>' . esc_html($name) . '</code></td>';
                    echo '<td>' . $size_str . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>هیچ Cache ای وجود ندارد</p>';
            }
            ?>
        </div>
    </div>
    <?php
}
