<?php
/**
 * صفحه ادمین برای ایجاد batch ارزها
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * اضافه کردن منو ادمین
 */
function cg_add_batch_creator_menu() {
    add_submenu_page(
        'crypto-sekhyab-options',
        'ایجاد خودکار ارزها',
        'ایجاد خودکار ارزها',
        'manage_options',
        'cg-batch-creator',
        'cg_batch_creator_page'
    );
}
add_action('admin_menu', 'cg_add_batch_creator_menu');

/**
 * صفحه Batch Creator
 */
function cg_batch_creator_page() {
    $api = cg_api();
    $total_coins = $api->get_total_coins_count();
    $existing_coins = wp_count_posts('cryptocurrency')->publish;
    
    // پردازش فرم
    if (isset($_POST['create_batch']) && check_admin_referer('cg_batch_action', 'cg_batch_nonce')) {
        $start_page = intval($_POST['start_page']);
        $end_page = intval($_POST['end_page']);
        
        if ($start_page > 0 && $end_page >= $start_page && $end_page <= 500) {
            set_time_limit(3600); // 1 ساعت
            
            $result = $api->create_coins_batch($start_page, $end_page);
            
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>✅ Batch با موفقیت اجرا شد!</strong><br>';
            echo 'ایجاد شده: ' . $result['created'] . '<br>';
            echo 'بروزرسانی شده: ' . $result['updated'] . '<br>';
            echo 'خطا: ' . $result['errors'] . '<br>';
            echo 'کل: ' . $result['total'];
            echo '</p></div>';
            
            $existing_coins = wp_count_posts('cryptocurrency')->publish;
        }
    }
    
    ?>
    <div class="wrap">
        <h1>🚀 ایجاد خودکار ارزهای دیجیتال</h1>
        
        <div class="cg-admin-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 24px;">
            
            <!-- فرم اصلی -->
            <div class="card" style="padding: 24px;">
                <h2>📊 وضعیت فعلی</h2>
                <div style="background: #f8fafc; padding: 20px; border-radius: 12px; margin: 20px 0;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                        <div>
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 8px;">کل ارزها در CoinGecko</div>
                            <div style="font-size: 32px; font-weight: 800; color: #0d1421;">
                                <?php echo number_format($total_coins); ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 8px;">ارزهای ایجاد شده</div>
                            <div style="font-size: 32px; font-weight: 800; color: #8dc63f;">
                                <?php echo number_format($existing_coins); ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 13px; color: #64748b; margin-bottom: 8px;">درصد پیشرفت</div>
                            <div style="font-size: 32px; font-weight: 800; color: #667eea;">
                                <?php echo number_format(($existing_coins / $total_coins) * 100, 1); ?>%
                            </div>
                        </div>
                    </div>
                </div>
                
                <h2>⚡ ایجاد Batch جدید</h2>
                <p>هر batch شامل 100 ارز می‌شود. برای مثال صفحه 1-5 = 500 ارز</p>
                
                <form method="post" style="margin-top: 24px;">
                    <?php wp_nonce_field('cg_batch_action', 'cg_batch_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="start_page">صفحه شروع</label></th>
                            <td>
                                <input type="number" 
                                       name="start_page" 
                                       id="start_page" 
                                       value="1" 
                                       min="1" 
                                       max="500" 
                                       class="regular-text">
                                <p class="description">از صفحه چند شروع شود؟</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="end_page">صفحه پایان</label></th>
                            <td>
                                <input type="number" 
                                       name="end_page" 
                                       id="end_page" 
                                       value="10" 
                                       min="1" 
                                       max="500" 
                                       class="regular-text">
                                <p class="description">تا صفحه چند ادامه یابد؟ (هر صفحه 100 ارز)</p>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 16px; border-radius: 8px; margin: 20px 0;">
                        <strong>⚠️ توجه:</strong>
                        <ul style="margin: 8px 0 0 20px;">
                            <li>این عملیات ممکن است چند دقیقه تا چند ساعت طول بکشد</li>
                            <li>صفحه را نبندید تا پردازش تمام شود</li>
                            <li>توصیه می‌شود batch های کوچک (5-10 صفحه) ایجاد کنید</li>
                            <li>بین هر batch 2 ثانیه صبر می‌شود</li>
                        </ul>
                    </div>
                    
                    <p>
                        <button type="submit" name="create_batch" class="button button-primary button-hero">
                            🚀 شروع ایجاد Batch
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- سایدبار راهنما -->
            <div>
                <div class="card" style="padding: 20px; margin-bottom: 20px;">
                    <h3>📚 راهنمای استفاده</h3>
                    <ol style="line-height: 2;">
                        <li>صفحه شروع و پایان را انتخاب کنید</li>
                        <li>روی "شروع ایجاد Batch" کلیک کنید</li>
                        <li>منتظر بمانید تا تمام شود</li>
                        <li>برای batch بعدی تکرار کنید</li>
                    </ol>
                </div>
                
                <div class="card" style="padding: 20px; margin-bottom: 20px;">
                    <h3>💡 توصیه‌ها</h3>
                    <ul style="line-height: 2;">
                        <li><strong>روز اول:</strong> صفحه 1-10 (1000 ارز برتر)</li>
                        <li><strong>روز دوم:</strong> صفحه 11-30 (2000 ارز دیگر)</li>
                        <li><strong>بعد:</strong> هر روز 10-20 صفحه</li>
                    </ul>
                </div>
                
                <div class="card" style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 style="color: white;">🎯 هدف</h3>
                    <p style="margin: 0; opacity: 0.95;">
                        ایجاد <?php echo number_format($total_coins); ?> صفحه برای تمام ارزهای CoinGecko
                    </p>
                </div>
                
                <div class="card" style="padding: 20px; margin-top: 20px;">
                    <h3>📊 آمار سریع</h3>
                    <table class="widefat">
                        <tr>
                            <td><strong>صفحه 1-10:</strong></td>
                            <td>1,000 ارز</td>
                        </tr>
                        <tr>
                            <td><strong>صفحه 1-50:</strong></td>
                            <td>5,000 ارز</td>
                        </tr>
                        <tr>
                            <td><strong>صفحه 1-100:</strong></td>
                            <td>10,000 ارز</td>
                        </tr>
                        <tr>
                            <td><strong>همه:</strong></td>
                            <td><?php echo number_format($total_coins); ?> ارز</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- لیست ارزهای موجود -->
        <div class="card" style="padding: 24px; margin-top: 24px;">
            <h2>📋 آخرین ارزهای ایجاد شده</h2>
            <?php
            $recent_cryptos = get_posts(array(
                'post_type' => 'cryptocurrency',
                'posts_per_page' => 20,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            if ($recent_cryptos) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>رتبه</th><th>نام</th><th>نماد</th><th>تاریخ ایجاد</th><th>عملیات</th></tr></thead>';
                echo '<tbody>';
                foreach ($recent_cryptos as $crypto) {
                    $rank = get_post_meta($crypto->ID, '_crypto_rank', true);
                    $symbol = get_post_meta($crypto->ID, '_crypto_symbol', true);
                    $date = get_the_date('Y/m/d H:i', $crypto->ID);
                    echo '<tr>';
                    echo '<td>#' . $rank . '</td>';
                    echo '<td><strong>' . esc_html($crypto->post_title) . '</strong></td>';
                    echo '<td><code>' . $symbol . '</code></td>';
                    echo '<td>' . $date . '</td>';
                    echo '<td><a href="' . get_permalink($crypto->ID) . '" target="_blank">مشاهده</a></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>هنوز ارزی ایجاد نشده است.</p>';
            }
            ?>
        </div>
    </div>
    
    <style>
    .cg-admin-grid .card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: none;
    }
    .cg-admin-grid h2, .cg-admin-grid h3 {
        margin-top: 0;
    }
    </style>
    <?php
}
