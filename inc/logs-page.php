<?php
/**
 * Logs admin page for Crypto Sekhyab theme
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

function crypto_sekhyab_add_logs_submenu() {
    add_submenu_page(
        'crypto-sekhyab-options',
        'لاگ‌ها',
        'لاگ‌ها',
        'manage_options',
        'crypto-sekhyab-logs',
        'crypto_sekhyab_render_logs_page',
        20
    );
}
add_action('admin_menu', 'crypto_sekhyab_add_logs_submenu');

function crypto_sekhyab_render_logs_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $lines_to_show = intval(get_option('crypto_sekhyab_log_lines', 300));
    if ($lines_to_show < 50) { $lines_to_show = 50; }

    $logs = crypto_sekhyab_read_logs($lines_to_show);
    $debug_log_path = WP_CONTENT_DIR . '/debug.log';
    $show_wp_debug = file_exists($debug_log_path);

    ?>
    <div class="wrap">
        <h1>📜 لاگ‌های قالب کریپتو سخیاب</h1>

        <div class="notice notice-info" style="margin-top:15px;">
            <p>
                مسیر فایل لاگ: <code><?php echo esc_html(crypto_sekhyab_get_log_file_path()); ?></code>
                <?php if ($show_wp_debug) : ?> | فایل Debug وردپرس: <code><?php echo esc_html($debug_log_path); ?></code><?php endif; ?>
            </p>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 10px 0;">
            <?php wp_nonce_field('crypto_sekhyab_logs_nonce'); ?>
            <input type="hidden" name="action" value="crypto_sekhyab_clear_logs" />
            <button class="button button-secondary" onclick="return confirm('آیا از پاک کردن لاگ‌ها مطمئن هستید؟');">پاک کردن لاگ‌ها</button>
            <a class="button button-primary" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=crypto_sekhyab_download_logs'), 'crypto_sekhyab_logs_nonce'); ?>">دانلود لاگ</a>
        </form>

        <h2 style="margin-top:30px;">لاگ‌های قالب (آخرین <?php echo intval($lines_to_show); ?> خط)</h2>
        <div style="background:#0f172a;color:#e2e8f0;border-radius:8px;padding:12px;max-height:500px;overflow:auto;font-family:monospace;">
            <?php if (!empty($logs)) : ?>
                <?php foreach ($logs as $line) : ?>
                    <div style="white-space:pre;line-height:1.5;border-bottom:1px solid rgba(255,255,255,0.06);padding:4px 0;">
                        <?php echo esc_html($line); ?>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div style="opacity:0.8;">لاگی یافت نشد.</div>
            <?php endif; ?>
        </div>

        <?php if ($show_wp_debug) : ?>
            <h2 style="margin-top:30px;">WP Debug Log (tail)</h2>
            <div style="background:#111827;color:#e5e7eb;border-radius:8px;padding:12px;max-height:400px;overflow:auto;font-family:monospace;">
                <?php
                $wp_debug_tail = array();
                $contents = @file_get_contents($debug_log_path);
                if ($contents) {
                    $lines = explode("\n", trim($contents));
                    $wp_debug_tail = array_slice($lines, -200);
                }
                if (!empty($wp_debug_tail)) {
                    foreach ($wp_debug_tail as $line) {
                        echo '<div style="white-space:pre;line-height:1.5;border-bottom:1px solid rgba(255,255,255,0.06);padding:4px 0;">' . esc_html($line) . '</div>';
                    }
                } else {
                    echo '<div style="opacity:0.8;">محتوایی برای نمایش موجود نیست.</div>';
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
