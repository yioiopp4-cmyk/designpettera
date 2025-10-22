<?php
/**
 * تنظیمات سفارشی‌سازی رنگ و ظاهر
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * افزودن صفحه تنظیمات
 */
function cg_add_customizer_menu() {
    add_submenu_page(
        'crypto-sekhyab-options',
        'ظاهر و رنگ‌بندی',
        'ظاهر و رنگ‌بندی',
        'manage_options',
        'cg-customizer',
        'cg_customizer_page'
    );
}
add_action('admin_menu', 'cg_add_customizer_menu');

/**
 * ثبت تنظیمات
 */
function cg_register_customizer_settings() {
    // رنگ‌ها
    register_setting('cg_customizer_options', 'cg_primary_color');
    register_setting('cg_customizer_options', 'cg_secondary_color');
    register_setting('cg_customizer_options', 'cg_menu_bg_color');
    register_setting('cg_customizer_options', 'cg_menu_text_color');
    register_setting('cg_customizer_options', 'cg_menu_hover_color');
    register_setting('cg_customizer_options', 'cg_menu_hover_bg_color');
    
    // نمایش بخش‌ها
    register_setting('cg_customizer_options', 'cg_show_hero');
    register_setting('cg_customizer_options', 'cg_show_market_stats');
    register_setting('cg_customizer_options', 'cg_show_crypto_table');
    register_setting('cg_customizer_options', 'cg_show_news');
}
add_action('admin_init', 'cg_register_customizer_settings');

/**
 * صفحه تنظیمات
 */
function cg_customizer_page() {
    if (isset($_POST['save_customizer']) && check_admin_referer('cg_customizer_action', 'cg_customizer_nonce')) {
        // ذخیره رنگ‌ها
        update_option('cg_primary_color', sanitize_hex_color($_POST['cg_primary_color']));
        update_option('cg_secondary_color', sanitize_hex_color($_POST['cg_secondary_color']));
        update_option('cg_menu_bg_color', sanitize_hex_color($_POST['cg_menu_bg_color']));
        update_option('cg_menu_text_color', sanitize_hex_color($_POST['cg_menu_text_color']));
        update_option('cg_menu_hover_color', sanitize_hex_color($_POST['cg_menu_hover_color']));
        update_option('cg_menu_hover_bg_color', sanitize_hex_color($_POST['cg_menu_hover_bg_color']));
        
        // ذخیره نمایش بخش‌ها
        update_option('cg_show_hero', isset($_POST['cg_show_hero']) ? '1' : '0');
        update_option('cg_show_market_stats', isset($_POST['cg_show_market_stats']) ? '1' : '0');
        update_option('cg_show_crypto_table', isset($_POST['cg_show_crypto_table']) ? '1' : '0');
        update_option('cg_show_news', isset($_POST['cg_show_news']) ? '1' : '0');
        
        echo '<div class="notice notice-success is-dismissible"><p><strong>✅ تنظیمات با موفقیت ذخیره شد!</strong></p></div>';
    }
    
    // دریافت مقادیر فعلی
    $primary_color = get_option('cg_primary_color', '#8dc63f');
    $secondary_color = get_option('cg_secondary_color', '#5e72e4');
    $menu_bg = get_option('cg_menu_bg_color', '#ffffff');
    $menu_text = get_option('cg_menu_text_color', '#1e293b');
    $menu_hover = get_option('cg_menu_hover_color', '#8dc63f');
    $menu_hover_bg = get_option('cg_menu_hover_bg_color', 'rgba(141, 198, 63, 0.1)');
    
    $show_hero = get_option('cg_show_hero', '1');
    $show_market_stats = get_option('cg_show_market_stats', '1');
    $show_crypto_table = get_option('cg_show_crypto_table', '1');
    $show_news = get_option('cg_show_news', '1');
    ?>
    <div class="wrap">
        <h1>🎨 ظاهر و رنگ‌بندی سایت</h1>
        <p>تنظیمات ظاهری سایت خود را سفارشی کنید</p>
        
        <form method="post" style="margin-top: 32px;">
            <?php wp_nonce_field('cg_customizer_action', 'cg_customizer_nonce'); ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                
                <!-- بخش اصلی -->
                <div>
                    <!-- رنگ‌ها -->
                    <div class="card" style="padding: 24px; margin-bottom: 24px;">
                        <h2>🎨 رنگ‌بندی</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="cg_primary_color">رنگ اصلی</label></th>
                                <td>
                                    <input type="color" 
                                           name="cg_primary_color" 
                                           id="cg_primary_color" 
                                           value="<?php echo esc_attr($primary_color); ?>">
                                    <p class="description">رنگ اصلی سایت (دکمه‌ها، لینک‌ها)</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="cg_secondary_color">رنگ ثانویه</label></th>
                                <td>
                                    <input type="color" 
                                           name="cg_secondary_color" 
                                           id="cg_secondary_color" 
                                           value="<?php echo esc_attr($secondary_color); ?>">
                                    <p class="description">رنگ ثانویه (gradient ها)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- منو -->
                    <div class="card" style="padding: 24px; margin-bottom: 24px;">
                        <h2>📋 منوی سایت</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="cg_menu_bg_color">پس‌زمینه منو</label></th>
                                <td>
                                    <input type="color" 
                                           name="cg_menu_bg_color" 
                                           id="cg_menu_bg_color" 
                                           value="<?php echo esc_attr($menu_bg); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="cg_menu_text_color">رنگ متن منو</label></th>
                                <td>
                                    <input type="color" 
                                           name="cg_menu_text_color" 
                                           id="cg_menu_text_color" 
                                           value="<?php echo esc_attr($menu_text); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="cg_menu_hover_color">رنگ hover منو</label></th>
                                <td>
                                    <input type="color" 
                                           name="cg_menu_hover_color" 
                                           id="cg_menu_hover_color" 
                                           value="<?php echo esc_attr($menu_hover); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th><label for="cg_menu_hover_bg_color">پس‌زمینه hover</label></th>
                                <td>
                                    <input type="text" 
                                           name="cg_menu_hover_bg_color" 
                                           id="cg_menu_hover_bg_color" 
                                           value="<?php echo esc_attr($menu_hover_bg); ?>"
                                           class="regular-text">
                                    <p class="description">می‌توانید از rgba استفاده کنید. مثال: rgba(141, 198, 63, 0.1)</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- نمایش بخش‌ها -->
                    <div class="card" style="padding: 24px;">
                        <h2>👁️ نمایش بخش‌ها در صفحه اصلی</h2>
                        
                        <table class="form-table">
                            <tr>
                                <th>بخش Hero</th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="cg_show_hero" 
                                               value="1" 
                                               <?php checked($show_hero, '1'); ?>>
                                        نمایش بخش Hero با عنوان و آمار
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>آمار بازار</th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="cg_show_market_stats" 
                                               value="1" 
                                               <?php checked($show_market_stats, '1'); ?>>
                                        نمایش آمار کلی بازار (Market Cap، Volume، ...)
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>جدول ارزها</th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="cg_show_crypto_table" 
                                               value="1" 
                                               <?php checked($show_crypto_table, '1'); ?>>
                                        نمایش جدول 100 ارز برتر
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>بخش اخبار</th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="cg_show_news" 
                                               value="1" 
                                               <?php checked($show_news, '1'); ?>>
                                        نمایش آخرین اخبار
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- پیش‌نمایش -->
                <div>
                    <div class="card" style="padding: 20px; position: sticky; top: 32px;">
                        <h3>👀 پیش‌نمایش</h3>
                        
                        <div style="border: 2px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin: 20px 0;">
                            <!-- منو -->
                            <div id="preview-menu" style="background: <?php echo esc_attr($menu_bg); ?>; padding: 16px; display: flex; gap: 16px; font-size: 13px;">
                                <span style="color: <?php echo esc_attr($menu_text); ?>;">صفحه اصلی</span>
                                <span style="color: <?php echo esc_attr($menu_hover); ?>; background: <?php echo esc_attr($menu_hover_bg); ?>; padding: 4px 12px; border-radius: 6px;">ارزها</span>
                                <span style="color: <?php echo esc_attr($menu_text); ?>;">اخبار</span>
                            </div>
                            
                            <!-- محتوا -->
                            <div style="padding: 20px; background: #f8fafc;">
                                <div style="background: <?php echo esc_attr($primary_color); ?>; color: white; padding: 12px; border-radius: 8px; margin-bottom: 12px; text-align: center; font-size: 12px;">
                                    دکمه نمونه
                                </div>
                                <div style="color: <?php echo esc_attr($primary_color); ?>; font-size: 12px; font-weight: 600;">
                                    لینک نمونه
                                </div>
                            </div>
                        </div>
                        
                        <p style="font-size: 12px; color: #64748b; margin: 16px 0 0 0;">
                            پس از ذخیره، صفحه را رفرش کنید تا تغییرات اعمال شود.
                        </p>
                    </div>
                    
                    <div class="card" style="padding: 20px; margin-top: 20px;">
                        <h3>💡 رنگ‌های پیشنهادی</h3>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 16px;">
                            <button type="button" onclick="setColors('#8dc63f', '#5e72e4')" class="button" style="background: linear-gradient(135deg, #8dc63f 0%, #5e72e4 100%); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer;">
                                سبز-آبی
                            </button>
                            <button type="button" onclick="setColors('#667eea', '#764ba2')" class="button" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer;">
                                بنفش
                            </button>
                            <button type="button" onclick="setColors('#f093fb', '#f5576c')" class="button" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer;">
                                صورتی
                            </button>
                            <button type="button" onclick="setColors('#4facfe', '#00f2fe')" class="button" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer;">
                                آبی
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <p style="margin-top: 24px;">
                <button type="submit" name="save_customizer" class="button button-primary button-hero">
                    💾 ذخیره تنظیمات
                </button>
                <a href="<?php echo home_url(); ?>" class="button button-large" target="_blank" style="margin-right: 12px;">
                    👁️ مشاهده سایت
                </a>
            </p>
        </form>
    </div>
    
    <script>
    function setColors(primary, secondary) {
        document.getElementById('cg_primary_color').value = primary;
        document.getElementById('cg_secondary_color').value = secondary;
        document.getElementById('cg_menu_hover_color').value = primary;
    }
    
    // Live preview
    document.querySelectorAll('input[type="color"], input[type="text"]').forEach(input => {
        input.addEventListener('input', function() {
            const id = this.id;
            const value = this.value;
            
            if (id === 'cg_menu_bg_color') {
                document.getElementById('preview-menu').style.background = value;
            } else if (id === 'cg_menu_text_color') {
                document.querySelectorAll('#preview-menu span')[0].style.color = value;
            } else if (id === 'cg_menu_hover_color') {
                document.querySelectorAll('#preview-menu span')[1].style.color = value;
            } else if (id === 'cg_menu_hover_bg_color') {
                document.querySelectorAll('#preview-menu span')[1].style.background = value;
            } else if (id === 'cg_primary_color') {
                document.querySelector('[style*="background:"]').style.background = value;
            }
        });
    });
    </script>
    <?php
}

/**
 * اعمال رنگ‌های سفارشی به سایت
 */
function cg_apply_custom_colors() {
    $primary = get_option('cg_primary_color', '#8dc63f');
    $secondary = get_option('cg_secondary_color', '#5e72e4');
    $menu_bg = get_option('cg_menu_bg_color', '#ffffff');
    $menu_text = get_option('cg_menu_text_color', '#1e293b');
    $menu_hover = get_option('cg_menu_hover_color', '#8dc63f');
    $menu_hover_bg = get_option('cg_menu_hover_bg_color', 'rgba(141, 198, 63, 0.1)');
    
    ?>
    <style>
    :root {
        --cg-primary: <?php echo esc_attr($primary); ?>;
        --cg-secondary: <?php echo esc_attr($secondary); ?>;
        --cg-menu-bg: <?php echo esc_attr($menu_bg); ?>;
        --cg-menu-text: <?php echo esc_attr($menu_text); ?>;
        --cg-menu-hover: <?php echo esc_attr($menu_hover); ?>;
        --cg-menu-hover-bg: <?php echo esc_attr($menu_hover_bg); ?>;
    }
    
    .cg-header {
        background: var(--cg-menu-bg) !important;
    }
    
    .cg-nav-item a {
        color: var(--cg-menu-text) !important;
    }
    
    .cg-nav-item a:hover {
        color: var(--cg-menu-hover) !important;
        background: var(--cg-menu-hover-bg) !important;
    }
    </style>
    <?php
}
add_action('wp_head', 'cg_apply_custom_colors');
