<?php
/**
 * توابع کمکی مربوط به ارزهای دیجیتال
 *
 * @package CryptoSekhyab
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

/**
 * دریافت لیست ارزهای دیجیتال
 */
function crypto_sekhyab_get_crypto_list($limit = 10) {
    $args = array(
        'post_type'      => 'cryptocurrency',
        'posts_per_page' => $limit,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    );
    
    return new WP_Query($args);
}

/**
 * دریافت قیمت ارز از CoinGecko
 */
function crypto_sekhyab_get_price($coingecko_id) {
    if (empty($coingecko_id)) {
        return false;
    }
    
    $cache_key = 'crypto_price_' . $coingecko_id;
    $cached_price = get_transient($cache_key);
    
    if ($cached_price !== false) {
        return $cached_price;
    }
    
    $api_key = get_option('crypto_sekhyab_coingecko_api_key', '');
    $api_url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . $coingecko_id . '&vs_currencies=usd&include_24hr_change=true&include_market_cap=true';
    
    if (!empty($api_key)) {
        $api_url .= '&x_cg_demo_api_key=' . $api_key;
    }
    
    $response = wp_remote_get($api_url, array('timeout' => 15));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!isset($data[$coingecko_id])) {
        return false;
    }
    
    $cache_duration = get_option('crypto_sekhyab_cache_duration', 120);
    set_transient($cache_key, $data[$coingecko_id], $cache_duration);
    
    return $data[$coingecko_id];
}

/**
 * تبدیل دلار به تومان
 */
function crypto_sekhyab_usd_to_irr($usd_amount) {
    $rate = get_option('crypto_sekhyab_usd_to_irr', 50000);
    return $usd_amount * $rate;
}

/**
 * فرمت کردن قیمت
 */
function crypto_sekhyab_format_price($price, $currency = 'usd') {
    if ($currency === 'usd') {
        return '$' . number_format($price, 2, '.', ',');
    } else {
        return number_format($price, 0, '.', ',') . ' تومان';
    }
}

/**
 * فرمت کردن تغییرات قیمت
 */
function crypto_sekhyab_format_change($change) {
    $class = $change >= 0 ? 'positive' : 'negative';
    $symbol = $change >= 0 ? '▲' : '▼';
    
    return sprintf(
        '<span class="price-change %s">%s %s%%</span>',
        $class,
        $symbol,
        number_format(abs($change), 2)
    );
}

/**
 * شورت‌کد نمایش لیست ارزها
 */
function crypto_sekhyab_crypto_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'columns' => 'name,price,change,market_cap',
    ), $atts);
    
    $query = crypto_sekhyab_get_crypto_list($atts['limit']);
    
    if (!$query->have_posts()) {
        return '<p>هیچ ارزی یافت نشد.</p>';
    }
    
    $output = '<div class="crypto-list-wrapper">';
    $output .= '<div class="crypto-list-header">';
    $output .= '<h2>لیست ارزهای دیجیتال</h2>';
    $output .= '<div class="currency-toggle">';
    $output .= '<button class="currency-btn active" data-currency="usd">دلار</button>';
    $output .= '<button class="currency-btn" data-currency="irr">تومان</button>';
    $output .= '</div>';
    $output .= '</div>';
    
    $output .= '<table class="crypto-table">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>نام ارز</th>';
    $output .= '<th>قیمت</th>';
    $output .= '<th>تغییرات 24 ساعته</th>';
    $output .= '<th>حجم بازار</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';
    
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $symbol = get_post_meta($post_id, '_crypto_symbol', true);
        $coingecko_id = get_post_meta($post_id, '_crypto_coingecko_id', true);
        $price_data = crypto_sekhyab_get_price($coingecko_id);
        
        $output .= '<tr data-link="' . get_permalink() . '">';
        
        // نام ارز
        $output .= '<td>';
        $output .= '<div class="crypto-name">';
        if (has_post_thumbnail()) {
            $output .= get_the_post_thumbnail($post_id, 'thumbnail', array('class' => 'crypto-icon'));
        }
        $output .= '<div>';
        $output .= '<strong>' . get_the_title() . '</strong><br>';
        $output .= '<span class="crypto-symbol">' . esc_html($symbol) . '</span>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</td>';
        
        // قیمت
        if ($price_data && isset($price_data['usd'])) {
            $usd_price = $price_data['usd'];
            $irr_price = crypto_sekhyab_usd_to_irr($usd_price);
            
            $output .= '<td class="crypto-price" data-usd-price="' . $usd_price . '">';
            $output .= crypto_sekhyab_format_price($usd_price, 'usd');
            $output .= '</td>';
            
            // تغییرات
            $output .= '<td>';
            if (isset($price_data['usd_24h_change'])) {
                $output .= crypto_sekhyab_format_change($price_data['usd_24h_change']);
            } else {
                $output .= '-';
            }
            $output .= '</td>';
            
            // حجم بازار
            $output .= '<td>';
            if (isset($price_data['usd_market_cap'])) {
                $market_cap = $price_data['usd_market_cap'];
                if ($market_cap >= 1000000000) {
                    $output .= '$' . number_format($market_cap / 1000000000, 2) . 'B';
                } else if ($market_cap >= 1000000) {
                    $output .= '$' . number_format($market_cap / 1000000, 2) . 'M';
                } else {
                    $output .= '$' . number_format($market_cap, 0);
                }
            } else {
                $output .= '-';
            }
            $output .= '</td>';
        } else {
            $output .= '<td colspan="3">در حال بارگذاری...</td>';
        }
        
        $output .= '</tr>';
    }
    
    wp_reset_postdata();
    
    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('crypto_list', 'crypto_sekhyab_crypto_list_shortcode');

/**
 * شورت‌کد نمایش قیمت یک ارز
 */
function crypto_sekhyab_crypto_price_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
        'currency' => 'usd',
    ), $atts);
    
    if (empty($atts['id'])) {
        return '';
    }
    
    $price_data = crypto_sekhyab_get_price($atts['id']);
    
    if (!$price_data || !isset($price_data['usd'])) {
        return '<span class="crypto-price-inline">در حال بارگذاری...</span>';
    }
    
    $price = $price_data['usd'];
    
    if ($atts['currency'] === 'irr') {
        $price = crypto_sekhyab_usd_to_irr($price);
    }
    
    return '<span class="crypto-price-inline">' . crypto_sekhyab_format_price($price, $atts['currency']) . '</span>';
}
add_shortcode('crypto_price', 'crypto_sekhyab_crypto_price_shortcode');
