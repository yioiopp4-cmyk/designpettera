<?php
/**
 * CoinMarketCap Batch Creator
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * افزودن منو
 */
function cmc_add_batch_menu() {
    add_submenu_page(
        'crypto-sekhyab-options',
        'ایجاد خودکار ارزها (CMC)',
        '🚀 ایجاد ارزها',
        'manage_options',
        'cmc-batch-creator',
        'cmc_batch_creator_page'
    );
}
add_action('admin_menu', 'cmc_add_batch_menu');

/**
 * صفحه Batch Creator
 */
function cmc_batch_creator_page() {
    $cmc = cmc_api();
    $existing_coins = wp_count_posts('cryptocurrency')->publish;
    
    // پردازش فرم
    if (isset($_POST['create_batch']) && check_admin_referer('cmc_batch_action', 'cmc_batch_nonce')) {
        $start = intval($_POST['start']);
        $end = intval($_POST['end']);
        
        if ($start > 0 && $end >= $start && $end <= 5000) {
            set_time_limit(3600); // 1 ساعت
            
            $result = $cmc->create_batch($start, $end, 100);
            
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>✅ Batch با موفقیت اجرا شد!</strong><br>';
            echo 'ایجاد شده: <strong>' . $result['created'] . '</strong><br>';
            echo 'بروزرسانی شده: <strong>' . $result['updated'] . '</strong><br>';
            echo 'خطا: ' . $result['errors'] . '<br>';
            echo '</p></div>';
            
            $existing_coins = wp_count_posts('cryptocurrency')->publish;
        }
    }
    
    ?>
    <div class="wrap">
        <h1>🚀 ایجاد خودکار ارزها - CoinMarketCap</h1>
        <p>ایجاد صفحات برای هزاران ارز به صورت خودکار با API رایگان CoinMarketCap</p>
        
        <div style="display: grid; grid-template-columns: 2.5fr 1fr; gap: 24px; margin-top: 32px;">
            
            <!-- فرم اصلی -->
            <div>
                <div class="card" style="padding: 28px; margin-bottom: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h2 style="color: white; display: flex; align-items: center; gap: 12px; margin: 0 0 20px 0;">
                        <span style="font-size: 36px;">📊</span>
                        وضعیت فعلی
                    </h2>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); padding: 20px; border-radius: 12px;">
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px;">ارزهای ایجاد شده</div>
                            <div style="font-size: 42px; font-weight: 900;"><?php echo number_format($existing_coins); ?></div>
                        </div>
                        <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); padding: 20px; border-radius: 12px;">
                            <div style="font-size: 13px; opacity: 0.9; margin-bottom: 8px;">منبع داده</div>
                            <div style="font-size: 32px; font-weight: 900;">CMC</div>
                        </div>
                    </div>
                </div>
                
                <div class="card" style="padding: 28px;">
                    <h2>⚡ ایجاد Batch جدید</h2>
                    <p>هر 100 ارز = 1 batch. مثال: 1-500 = 5 batch = 500 ارز</p>
                    
                    <form method="post" style="margin-top: 24px;">
                        <?php wp_nonce_field('cmc_batch_action', 'cmc_batch_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="start">شروع از ارز شماره</label></th>
                                <td>
                                    <input type="number" 
                                           name="start" 
                                           id="start" 
                                           value="1" 
                                           min="1" 
                                           max="5000" 
                                           class="regular-text">
                                    <p class="description">از ارز چندم شروع شود؟ (معمولاً 1)</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="end">پایان در ارز شماره</label></th>
                                <td>
                                    <input type="number" 
                                           name="end" 
                                           id="end" 
                                           value="500" 
                                           min="1" 
                                           max="5000" 
                                           class="regular-text">
                                    <p class="description">تا ارز چندم ادامه یابد؟ (توصیه: 500-1000)</p>
                                </td>
                            </tr>
                        </table>
                        
                        <div style="background: #fff3cd; border-right: 4px solid #ffc107; padding: 20px; border-radius: 8px; margin: 24px 0;">
                            <strong>⚠️ نکات مهم:</strong>
                            <ul style="margin: 12px 0 0 20px; line-height: 1.8;">
                                <li>این عملیات ممکن است چند دقیقه طول بکشد</li>
                                <li>صفحه را تا پایان کار نبندید</li>
                                <li>برای بار اول: 1-500 (500 ارز برتر)</li>
                                <li>بار دوم: 501-1000 (500 ارز بعدی)</li>
                                <li>با API رایگان: حداکثر 1000 ارز/روز</li>
                            </ul>
                        </div>
                        
                        <p>
                            <button type="submit" name="create_batch" class="button button-primary button-hero">
                                🚀 شروع ایجاد Batch
                            </button>
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- سایدبار -->
            <div>
                <div class="card" style="padding: 20px; margin-bottom: 20px;">
                    <h3>💡 توصیه‌ها</h3>
                    <div style="font-size: 14px; line-height: 1.8;">
                        <p><strong>روز اول:</strong></p>
                        <ul style="margin: 8px 0 16px 20px;">
                            <li>1-500 (500 ارز برتر)</li>
                        </ul>
                        
                        <p><strong>روز دوم:</strong></p>
                        <ul style="margin: 8px 0 16px 20px;">
                            <li>501-1000 (500 ارز بعدی)</li>
                        </ul>
                        
                        <p><strong>روز سوم:</strong></p>
                        <ul style="margin: 8px 0 16px 20px;">
                            <li>1001-1500 (500 ارز بعدی)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card" style="padding: 20px; margin-bottom: 20px; background: #d4edda; border: 2px solid #28a745;">
                    <h3 style="color: #155724; margin: 0 0 12px 0;">✅ مزایای CMC</h3>
                    <ul style="color: #155724; font-size: 14px; line-height: 1.8;">
                        <li>✅ کاملاً رایگان</li>
                        <li>✅ 10,000 request/ماه</li>
                        <li>✅ داده‌های دقیق</li>
                        <li>✅ بروزرسانی real-time</li>
                    </ul>
                </div>
                
                <div class="card" style="padding: 20px;">
                    <h3>📊 آمار سریع</h3>
                    <table class="widefat" style="margin-top: 12px;">
                        <tr>
                            <td><strong>1-500:</strong></td>
                            <td>500 ارز برتر</td>
                        </tr>
                        <tr>
                            <td><strong>1-1000:</strong></td>
                            <td>1,000 ارز</td>
                        </tr>
                        <tr>
                            <td><strong>1-5000:</strong></td>
                            <td>5,000 ارز</td>
                        </tr>
                        <tr style="background: #f0fdf4;">
                            <td><strong>موجود:</strong></td>
                            <td><strong><?php echo number_format($existing_coins); ?> ارز</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- لیست ارزها -->
        <div class="card" style="padding: 24px; margin-top: 24px;">
            <h2>📋 آخرین ارزهای ایجاد شده</h2>
            <?php
            $recent = get_posts(array(
                'post_type' => 'cryptocurrency',
                'posts_per_page' => 20,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            if ($recent) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>رتبه</th><th>نام</th><th>نماد</th><th>تاریخ</th><th>لینک</th></tr></thead>';
                echo '<tbody>';
                foreach ($recent as $crypto) {
                    $rank = get_post_meta($crypto->ID, '_crypto_rank', true);
                    $symbol = get_post_meta($crypto->ID, '_crypto_symbol', true);
                    echo '<tr>';
                    echo '<td>#' . $rank . '</td>';
                    echo '<td><strong>' . esc_html($crypto->post_title) . '</strong></td>';
                    echo '<td><code>' . $symbol . '</code></td>';
                    echo '<td>' . get_the_date('Y/m/d H:i', $crypto->ID) . '</td>';
                    echo '<td><a href="' . get_permalink($crypto->ID) . '" target="_blank">مشاهده →</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #64748b;">';
                echo '<div style="font-size: 48px; margin-bottom: 16px;">📊</div>';
                echo '<p>هنوز ارزی ایجاد نشده است. فرم بالا را پر کنید و batch اول را ایجاد کنید!</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php
}
