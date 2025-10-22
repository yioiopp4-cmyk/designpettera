<?php
/**
 * سیستم خودکار ایجاد صفحات ارز از API
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ایجاد خودکار پست‌های ارز از API - شروع از رتبه های برتر
 */
function crypto_sekhyab_auto_create_cryptos() {
    // دریافت لیست 100 ارز برتر (از رتبه 1 تا 100)
    // با order=market_cap_desc که از برترین‌ها شروع می‌کند
    $cryptos = crypto_sekhyab_get_top_cryptos(100, 1);
    
    if (empty($cryptos) || !is_array($cryptos)) {
        crypto_sekhyab_log('ERROR', 'Failed to fetch cryptos from API');
        return false;
    }
    
    crypto_sekhyab_log('INFO', 'Starting auto-create for ' . count($cryptos) . ' cryptocurrencies from top ranks');
    
    $created = 0;
    $updated = 0;
    
    foreach ($cryptos as $crypto) {
        if (!is_array($crypto) || !isset($crypto['id'])) {
            continue;
        }
        
        // چک کنیم آیا این ارز قبلاً ساخته شده
        $existing = get_posts(array(
            'post_type' => 'cryptocurrency',
            'meta_key' => '_crypto_coingecko_id',
            'meta_value' => $crypto['id'],
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));
        
        if (!empty($existing)) {
            // بروزرسانی پست موجود
            $post_id = $existing[0]->ID;
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $crypto['name'] . ' (' . strtoupper($crypto['symbol']) . ')',
                'post_content' => crypto_sekhyab_generate_crypto_content($crypto),
                'post_status' => 'publish'
            ));
            crypto_sekhyab_log('DEBUG', 'Updated crypto: ' . $crypto['name']);
            $updated++;
        } else {
            // ایجاد پست جدید
            $post_id = wp_insert_post(array(
                'post_type' => 'cryptocurrency',
                'post_title' => $crypto['name'] . ' (' . strtoupper($crypto['symbol']) . ')',
                'post_content' => crypto_sekhyab_generate_crypto_content($crypto),
                'post_status' => 'publish',
                'post_author' => 1
            ));
            
            if (!is_wp_error($post_id)) {
                crypto_sekhyab_log('DEBUG', 'Created crypto: ' . $crypto['name'] . ' (Rank: ' . ($crypto['market_cap_rank'] ?? 'N/A') . ')');
                $created++;
            } else {
                crypto_sekhyab_log('ERROR', 'Failed to create crypto: ' . $crypto['name'] . ' - ' . $post_id->get_error_message());
            }
        }
        
        if (!is_wp_error($post_id) && $post_id > 0) {
            // ذخیره متادیتا
            update_post_meta($post_id, '_crypto_symbol', strtoupper($crypto['symbol']));
            update_post_meta($post_id, '_crypto_coingecko_id', $crypto['id']);
            update_post_meta($post_id, '_crypto_rank', $crypto['market_cap_rank'] ?? 0);
            update_post_meta($post_id, '_crypto_image', $crypto['image'] ?? '');
            
            // نماد TradingView
            $tv_symbol = crypto_sekhyab_get_tradingview_symbol($crypto['symbol']);
            update_post_meta($post_id, '_crypto_tradingview_symbol', $tv_symbol);
            
            // تصویر شاخص
            if (!empty($crypto['image'])) {
                crypto_sekhyab_set_crypto_thumbnail($post_id, $crypto['image']);
            }
        }
    }
    
    crypto_sekhyab_log('SUCCESS', "Auto crypto creator completed: Created=$created, Updated=$updated");
    
    return array(
        'created' => $created,
        'updated' => $updated,
        'total' => count($cryptos)
    );
}

/**
 * تولید محتوای خودکار برای ارز
 */
function crypto_sekhyab_generate_crypto_content($crypto) {
    $name = $crypto['name'] ?? 'ارز دیجیتال';
    $symbol = strtoupper($crypto['symbol'] ?? '');
    
    $content = "<h2>درباره $name ($symbol)</h2>\n\n";
    $content .= "<p>$name یکی از ارزهای دیجیتال است که با نماد $symbol شناخته می‌شود. در این صفحه می‌توانید قیمت لحظه‌ای، نمودار قیمت و اطلاعات کامل $name را مشاهده کنید.</p>\n\n";
    
    $content .= "<h3>ویژگی‌های $name</h3>\n";
    $content .= "<ul>\n";
    $content .= "<li>نماد: $symbol</li>\n";
    
    if (isset($crypto['market_cap_rank'])) {
        $content .= "<li>رتبه بازار: {$crypto['market_cap_rank']}</li>\n";
    }
    
    $content .= "<li>قیمت لحظه‌ای به‌روز با نمودار تعاملی</li>\n";
    $content .= "<li>تاریخچه تغییرات قیمت</li>\n";
    $content .= "<li>حجم معاملات 24 ساعته</li>\n";
    $content .= "</ul>\n\n";
    
    $content .= "<p>برای مشاهده قیمت دقیق و به‌روز $name، نمودار بالای این صفحه را مشاهده کنید. همچنین می‌توانید $name را با سایر ارزهای دیجیتال مقایسه کنید.</p>";
    
    return $content;
}

/**
 * تبدیل نماد ارز به نماد TradingView
 */
function crypto_sekhyab_get_tradingview_symbol($symbol) {
    $symbol = strtoupper($symbol);
    
    // نقشه‌برداری نمادهای خاص
    $mapping = array(
        'BTC' => 'BINANCE:BTCUSDT',
        'ETH' => 'BINANCE:ETHUSDT',
        'BNB' => 'BINANCE:BNBUSDT',
        'USDT' => 'BINANCE:USDTUSD',
        'XRP' => 'BINANCE:XRPUSDT',
        'ADA' => 'BINANCE:ADAUSDT',
        'DOGE' => 'BINANCE:DOGEUSDT',
        'SOL' => 'BINANCE:SOLUSDT',
        'DOT' => 'BINANCE:DOTUSDT',
        'MATIC' => 'BINANCE:MATICUSDT'
    );
    
    if (isset($mapping[$symbol])) {
        return $mapping[$symbol];
    }
    
    // پیش‌فرض
    return "BINANCE:{$symbol}USDT";
}

/**
 * تنظیم تصویر شاخص از URL
 */
function crypto_sekhyab_set_crypto_thumbnail($post_id, $image_url) {
    // چک کنیم آیا قبلاً تصویر داریم
    if (has_post_thumbnail($post_id)) {
        return;
    }
    
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');
    
    if (!is_wp_error($attachment_id)) {
        set_post_thumbnail($post_id, $attachment_id);
    }
}

/**
 * افزودن Cron Job برای بروزرسانی خودکار
 */
function crypto_sekhyab_schedule_auto_update() {
    if (!wp_next_scheduled('crypto_sekhyab_auto_update_cryptos')) {
        wp_schedule_event(time(), 'daily', 'crypto_sekhyab_auto_update_cryptos');
    }
}
add_action('wp', 'crypto_sekhyab_schedule_auto_update');

add_action('crypto_sekhyab_auto_update_cryptos', 'crypto_sekhyab_auto_create_cryptos');

/**
 * دکمه ایجاد دستی در ادمین
 */
function crypto_sekhyab_add_manual_sync_button() {
    add_submenu_page(
        'crypto-sekhyab-options',
        'همگام‌سازی ارزها',
        'همگام‌سازی ارزها',
        'manage_options',
        'crypto-sync',
        'crypto_sekhyab_sync_page'
    );
}
add_action('admin_menu', 'crypto_sekhyab_add_manual_sync_button');

/**
 * صفحه همگام‌سازی
 */
function crypto_sekhyab_sync_page() {
    if (isset($_POST['sync_cryptos']) && check_admin_referer('crypto_sync_action', 'crypto_sync_nonce')) {
        $result = crypto_sekhyab_auto_create_cryptos();
        
        if ($result) {
            echo '<div class="notice notice-success"><p>';
            echo '✅ همگام‌سازی با موفقیت انجام شد!<br>';
            echo "ایجاد شده: {$result['created']}<br>";
            echo "بروزرسانی شده: {$result['updated']}<br>";
            echo "کل: {$result['total']}";
            echo '</p></div>';
        }
    }
    
    $crypto_count = wp_count_posts('cryptocurrency')->publish;
    
    ?>
    <div class="wrap">
        <h1>🔄 همگام‌سازی ارزهای دیجیتال</h1>
        
        <div class="card" style="max-width: 800px;">
            <h2>وضعیت فعلی</h2>
            <p><strong>تعداد ارزهای موجود:</strong> <?php echo $crypto_count; ?></p>
            <p>این ابزار به صورت خودکار 100 ارز برتر را از CoinGecko دریافت کرده و صفحات آنها را ایجاد یا بروزرسانی می‌کند.</p>
            
            <h3>ویژگی‌ها:</h3>
            <ul>
                <li>✅ ایجاد خودکار صفحه برای هر ارز</li>
                <li>✅ بروزرسانی خودکار روزانه</li>
                <li>✅ تصویر، نماد و اطلاعات کامل</li>
                <li>✅ نمودار TradingView</li>
                <li>✅ قیمت لحظه‌ای</li>
            </ul>
            
            <form method="post">
                <?php wp_nonce_field('crypto_sync_action', 'crypto_sync_nonce'); ?>
                <p>
                    <button type="submit" name="sync_cryptos" class="button button-primary button-hero">
                        🔄 همگام‌سازی الان
                    </button>
                </p>
                <p class="description">این عملیات ممکن است چند دقیقه طول بکشد.</p>
            </form>
        </div>
        
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2>📊 لیست ارزهای موجود</h2>
            <?php
            $cryptos = get_posts(array(
                'post_type' => 'cryptocurrency',
                'posts_per_page' => 10,
                'orderby' => 'meta_value_num',
                'meta_key' => '_crypto_rank',
                'order' => 'ASC'
            ));
            
            if ($cryptos) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>رتبه</th><th>نام</th><th>نماد</th><th>لینک</th></tr></thead>';
                echo '<tbody>';
                foreach ($cryptos as $crypto) {
                    $rank = get_post_meta($crypto->ID, '_crypto_rank', true);
                    $symbol = get_post_meta($crypto->ID, '_crypto_symbol', true);
                    echo '<tr>';
                    echo '<td>' . $rank . '</td>';
                    echo '<td>' . esc_html($crypto->post_title) . '</td>';
                    echo '<td><code>' . $symbol . '</code></td>';
                    echo '<td><a href="' . get_permalink($crypto->ID) . '" target="_blank">مشاهده</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
                
                if ($crypto_count > 10) {
                    echo '<p><a href="' . admin_url('edit.php?post_type=cryptocurrency') . '" class="button">مشاهده همه (' . $crypto_count . ')</a></p>';
                }
            } else {
                echo '<p>هنوز ارزی ایجاد نشده است. روی دکمه "همگام‌سازی الان" کلیک کنید.</p>';
            }
            ?>
        </div>
    </div>
    
    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            padding: 20px;
            margin: 20px 0;
        }
        .card h2 {
            margin-top: 0;
        }
        .card ul {
            line-height: 2;
        }
    </style>
    <?php
}
