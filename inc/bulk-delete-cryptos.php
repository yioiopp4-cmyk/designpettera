<?php
/**
 * ابزار حذف گروهی ارزها (امن)
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * افزودن زیرمنو زیر منوی «ارزهای دیجیتال» (CPT)
 */
function cg_add_bulk_delete_cryptos_menu() {
    add_submenu_page(
        'edit.php?post_type=cryptocurrency',
        'پاک کردن همه ارزها',
        'پاک کردن همه ارزها',
        'manage_options',
        'cg-bulk-delete-cryptos',
        'cg_bulk_delete_cryptos_page'
    );
}
add_action('admin_menu', 'cg_add_bulk_delete_cryptos_menu');

/**
 * صفحه حذف گروهی
 */
function cg_bulk_delete_cryptos_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $message = '';
    $deleted  = 0;
    $trashed  = 0;

    if (isset($_POST['cg_bulk_delete_submit']) && check_admin_referer('cg_bulk_delete_cryptos', 'cg_bulk_delete_nonce')) {
        $hard_delete = isset($_POST['hard_delete']) && $_POST['hard_delete'] === '1';

        // پردازش در batch های 200 تایی برای جلوگیری از timeout
        $paged = 1;
        $per_page = 200;

        do {
            $ids = get_posts(array(
                'post_type'      => 'cryptocurrency',
                'post_status'    => 'any',
                'fields'         => 'ids',
                'posts_per_page' => $per_page,
                'paged'          => $paged,
                'no_found_rows'  => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ));

            if (empty($ids)) {
                break;
            }

            foreach ($ids as $post_id) {
                if ($hard_delete) {
                    $ok = wp_delete_post($post_id, true);
                    if ($ok) { $deleted++; }
                } else {
                    $ok = wp_trash_post($post_id);
                    if ($ok) { $trashed++; }
                }
            }

            $paged++;
            // فشار کمتر به سرور
            usleep(100000); // 0.1s
        } while (true);

        // پاک کردن cache های مرتبط با لیست بازار برای تازه‌سازی سریع
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_top_cryptos_%' OR option_name LIKE '_transient_timeout_top_cryptos_%'");

        $result_msg = $hard_delete
            ? ("تعداد حذف دائم: " . number_format($deleted))
            : ("تعداد انتقال به زباله‌دان: " . number_format($trashed));

        $message = '<div class="notice notice-success is-dismissible"><p><strong>✅ عملیات انجام شد.</strong><br>' . esc_html($result_msg) . '</p></div>';
    }

    $count_published = wp_count_posts('cryptocurrency')->publish;
    $count_trash     = wp_count_posts('cryptocurrency')->trash;
    ?>
    <div class="wrap">
        <h1>🗑️ پاک کردن همه ارزها</h1>

        <?php echo $message; ?>

        <div class="card" style="max-width: 900px; padding: 24px;">
            <p>این ابزار همه پست‌های نوع <code>cryptocurrency</code> را به صورت امن پاک می‌کند تا بتوانید دوباره آنها را بسازید.</p>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:20px 0;">
                <div style="background:#f8fafc;padding:16px;border-radius:12px;">
                    <div style="font-size:13px;color:#64748b;">تعداد منتشرشده</div>
                    <div style="font-size:28px;font-weight:800;"><?php echo number_format((int) $count_published); ?></div>
                </div>
                <div style="background:#fff7ed;padding:16px;border-radius:12px;">
                    <div style="font-size:13px;color:#9a3412;">تعداد در زباله‌دان</div>
                    <div style="font-size:28px;font-weight:800;color:#9a3412;"><?php echo number_format((int) $count_trash); ?></div>
                </div>
                <div style="background:#eff6ff;padding:16px;border-radius:12px;">
                    <div style="font-size:13px;color:#1d4ed8;">Cache بازار</div>
                    <div style="font-size:28px;font-weight:800;color:#1d4ed8;">قابل بازسازی</div>
                </div>
            </div>

            <form method="post" onsubmit="return confirm('آیا از پاک کردن همه ارزها مطمئن هستید؟');">
                <?php wp_nonce_field('cg_bulk_delete_cryptos', 'cg_bulk_delete_nonce'); ?>

                <label style="display:flex;align-items:center;gap:8px;margin:12px 0;">
                    <input type="checkbox" name="hard_delete" value="1">
                    <span>حذف دائم (بدون انتقال به زباله‌دان)</span>
                </label>

                <p class="description" style="margin:8px 0 16px;">پیشنهاد می‌شود ابتدا انتقال به زباله‌دان را انجام دهید. سپس در صورت نیاز، به صورت دائم حذف کنید.</p>

                <button type="submit" name="cg_bulk_delete_submit" class="button button-primary button-hero" style="background:#ef4444;border-color:#ef4444;">
                    🗑️ پاک کردن همه ارزها
                </button>

                <a href="<?php echo admin_url('admin.php?page=cg-cache-cleaner'); ?>" class="button" style="margin-right:8px;">🧹 پاک کردن Cache</a>
            </form>
        </div>
    </div>
    <?php
}
