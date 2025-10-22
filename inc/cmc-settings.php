<?php
/**
 * تنظیمات CoinMarketCap
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * افزودن منو تنظیمات
 */
function cmc_add_settings_menu() {
    // غیرفعال - فقط از CoinGecko استفاده می‌شود
    return;
    
    add_submenu_page(
        'crypto-sekhyab-options',
        'تنظیمات CoinMarketCap',
        '⚙️ تنظیمات API',
        'manage_options',
        'cmc-settings',
        'cmc_settings_page'
    );
}
// add_action('admin_menu', 'cmc_add_settings_menu'); // غیرفعال

/**
 * صفحه تنظیمات
 */
function cmc_settings_page() {
    if (isset($_POST['save_cmc_settings']) && check_admin_referer('cmc_settings_action', 'cmc_settings_nonce')) {
        update_option('cmc_api_key', sanitize_text_field($_POST['cmc_api_key']));
        update_option('crypto_api_source', sanitize_text_field($_POST['crypto_api_source']));
        update_option('crypto_sekhyab_usdt_price', intval($_POST['usdt_price']));
        
        echo '<div class="notice notice-success is-dismissible"><p><strong>✅ تنظیمات با موفقیت ذخیره شد!</strong></p></div>';
    }
    
    $api_key = get_option('cmc_api_key', 'c53a3219-e9f0-47aa-8508-d4cbafb591af');
    $api_source = get_option('crypto_api_source', 'coinmarketcap');
    $usdt_price = get_option('crypto_sekhyab_usdt_price', '114850');
    
    // تست API
    $api_status = 'unknown';
    $api_message = '';
    
    if ($api_source == 'coinmarketcap') {
        $cmc = cmc_api();
        $test = $cmc->get_global_metrics();
        if ($test) {
            $api_status = 'success';
            $api_message = 'اتصال به CoinMarketCap موفق - ' . number_format($test['active_cryptocurrencies'] ?? 0) . ' ارز فعال';
        } else {
            $api_status = 'error';
            $api_message = 'خطا در اتصال به CoinMarketCap API';
        }
    }
    
    ?>
    <div class="wrap">
        <h1>⚙️ تنظیمات CoinMarketCap API</h1>
        <p>کلید API رایگان خود را وارد کنید و منبع داده را انتخاب کنید</p>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 32px;">
            
            <!-- فرم اصلی -->
            <div>
                <form method="post">
                    <?php wp_nonce_field('cmc_settings_action', 'cmc_settings_nonce'); ?>
                    
                    <!-- منبع API -->
                    <div class="card" style="padding: 24px; margin-bottom: 24px;">
                        <h2>🔌 منبع داده</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="crypto_api_source">انتخاب منبع</label></th>
                                <td>
                                    <select name="crypto_api_source" id="crypto_api_source" class="regular-text">
                                        <option value="coinmarketcap" <?php selected($api_source, 'coinmarketcap'); ?>>
                                            CoinMarketCap (رایگان - توصیه می‌شود) ⭐
                                        </option>
                                        <option value="coingecko" <?php selected($api_source, 'coingecko'); ?>>
                                            CoinGecko
                                        </option>
                                    </select>
                                    <p class="description">
                                        <strong>توصیه:</strong> CoinMarketCap سریع‌تر و دقیق‌تر است
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- کلید API -->
                    <div class="card" style="padding: 24px; margin-bottom: 24px;">
                        <h2>🔑 کلید API CoinMarketCap</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="cmc_api_key">API Key</label></th>
                                <td>
                                    <input type="text" 
                                           name="cmc_api_key" 
                                           id="cmc_api_key" 
                                           value="<?php echo esc_attr($api_key); ?>" 
                                           class="large-text code">
                                    <p class="description">
                                        کلید API رایگان شما: <code>c53a3219-e9f0-47aa-8508-d4cbafb591af</code><br>
                                        <a href="https://pro.coinmarketcap.com/signup" target="_blank">دریافت API Key رایگان →</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- نرخ تتر -->
                    <div class="card" style="padding: 24px; margin-bottom: 24px;">
                        <h2>💰 نرخ تبدیل</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="usdt_price">نرخ تتر (تومان)</label></th>
                                <td>
                                    <input type="number" 
                                           name="usdt_price" 
                                           id="usdt_price" 
                                           value="<?php echo esc_attr($usdt_price); ?>" 
                                           class="regular-text">
                                    <p class="description">
                                        نرخ هر دلار (USDT) به تومان - برای تبدیل قیمت‌ها
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <p>
                        <button type="submit" name="save_cmc_settings" class="button button-primary button-hero">
                            💾 ذخیره تنظیمات
                        </button>
                    </p>
                </form>
            </div>
            
            <!-- سایدبار -->
            <div>
                <!-- وضعیت API -->
                <div class="card" style="padding: 20px; margin-bottom: 20px;">
                    <h3>📊 وضعیت API</h3>
                    
                    <?php if ($api_status == 'success') : ?>
                    <div style="background: #d4edda; border: 2px solid #28a745; padding: 16px; border-radius: 8px; margin-top: 16px;">
                        <div style="color: #155724; font-weight: 700; margin-bottom: 8px;">✅ اتصال موفق</div>
                        <div style="color: #155724; font-size: 13px;"><?php echo $api_message; ?></div>
                    </div>
                    <?php elseif ($api_status == 'error') : ?>
                    <div style="background: #f8d7da; border: 2px solid #dc3545; padding: 16px; border-radius: 8px; margin-top: 16px;">
                        <div style="color: #721c24; font-weight: 700; margin-bottom: 8px;">❌ خطا</div>
                        <div style="color: #721c24; font-size: 13px;"><?php echo $api_message; ?></div>
                    </div>
                    <?php else : ?>
                    <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 16px; border-radius: 8px; margin-top: 16px;">
                        <div style="color: #856404; font-weight: 700; margin-bottom: 8px;">⏳ در حال تست...</div>
                        <div style="color: #856404; font-size: 13px;">تنظیمات را ذخیره کنید</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- راهنما -->
                <div class="card" style="padding: 20px; margin-bottom: 20px;">
                    <h3>📚 راهنمای استفاده</h3>
                    <ol style="line-height: 2; font-size: 14px;">
                        <li>کلید API را وارد کنید</li>
                        <li>منبع را انتخاب کنید</li>
                        <li>نرخ تتر را تنظیم کنید</li>
                        <li>ذخیره کنید</li>
                        <li>Cache را پاک کنید</li>
                    </ol>
                </div>
                
                <!-- محدودیت‌ها -->
                <div class="card" style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 style="color: white;">⚡ پلن رایگان CMC</h3>
                    <ul style="line-height: 2; font-size: 14px; opacity: 0.95;">
                        <li>✅ 10,000 درخواست/ماه</li>
                        <li>✅ همه ارزها</li>
                        <li>✅ قیمت لحظه‌ای</li>
                        <li>✅ آمار کامل</li>
                    </ul>
                </div>
                
                <!-- لینک‌ها -->
                <div class="card" style="padding: 20px;">
                    <h3>🔗 لینک‌های مفید</h3>
                    <ul style="line-height: 2; font-size: 14px;">
                        <li><a href="https://coinmarketcap.com/api/" target="_blank">مستندات API →</a></li>
                        <li><a href="https://pro.coinmarketcap.com/account" target="_blank">پنل کاربری →</a></li>
                        <li><a href="<?php echo admin_url('admin.php?page=cg-cache-cleaner'); ?>">پاک‌سازی Cache →</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * فیلد Select
 */
function crypto_sekhyab_select_field($args) {
    $option = get_option($args['label_for'], $args['default'] ?? '');
    $options = $args['options'] ?? array();
    ?>
    <select id="<?php echo esc_attr($args['label_for']); ?>" 
            name="<?php echo esc_attr($args['label_for']); ?>" 
            class="regular-text">
        <?php foreach ($options as $value => $label) : ?>
            <option value="<?php echo esc_attr($value); ?>" <?php selected($option, $value); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description"><?php echo esc_html($args['description']); ?></p>
    <?php
}
