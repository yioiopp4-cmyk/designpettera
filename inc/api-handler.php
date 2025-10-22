<?php
/**
 * مدیریت API ها - دریافت خودکار لیست ارزها و نرخ تتر
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * دریافت نرخ تتر لحظه‌ای از API نوبیتکس
 */
function crypto_sekhyab_get_usdt_price() {
    crypto_sekhyab_log('DEBUG', 'Fetching USDT/IRT rate');
    
    $cache_key = 'usdt_irr_price';
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        crypto_sekhyab_log('DEBUG', 'USDT rate from cache', array('rate' => $cached));
        return (float) $cached;
    }
    
    // 1) Nobitex market stats (prefer latest) - faster endpoint
    $nbx_url = 'https://api.nobitex.ir/market/stats?symbol=USDTIRT';
    $response = wp_remote_get($nbx_url, array(
        'timeout' => 8,
        'headers' => array('Accept' => 'application/json'),
        'redirection' => 2,
        'reject_unsafe_urls' => false,
    ));
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        // Try possible shapes
        $latest = null;
        if (isset($data['stats']['USDTIRT']['latest'])) {
            $latest = $data['stats']['USDTIRT']['latest'];
        } elseif (isset($data['global']['USDTIRT']['latest'])) {
            $latest = $data['global']['USDTIRT']['latest'];
        }
        if ($latest) {
            $usdt_price = (float) $latest;
            set_transient($cache_key, $usdt_price, 10);
            crypto_sekhyab_log('INFO', 'USDT rate fetched from Nobitex stats', array('rate' => $usdt_price));
            return $usdt_price;
        } else {
            crypto_sekhyab_log('WARNING', 'Nobitex stats did not include latest', array('data' => $data));
        }
    } else {
        crypto_sekhyab_log('ERROR', 'Nobitex stats request failed', array('error' => $response->get_error_message()));
    }

    // 2) Tetherland backup (defensive parsing)
    $backup_url = 'https://api.tetherland.com/currencies';
    $backup_response = wp_remote_get($backup_url, array('timeout' => 12));
    if (!is_wp_error($backup_response)) {
        crypto_sekhyab_log('WARNING', 'Using backup USDT rate API (tetherland)');
        $backup_body = wp_remote_retrieve_body($backup_response);
        $backup_data = json_decode($backup_body, true);
        $usdt_price = null;
        // Possible shapes
        if (isset($backup_data['data']['currencies']['USDT']['price'])) {
            $usdt_price = (float) $backup_data['data']['currencies']['USDT']['price'];
        } elseif (isset($backup_data['currencies']) && is_array($backup_data['currencies'])) {
            foreach ($backup_data['currencies'] as $item) {
                if ((isset($item['symbol']) && strtoupper($item['symbol']) === 'USDT') || (isset($item['name']) && stripos($item['name'], 'USDT') !== false)) {
                    if (isset($item['price'])) { $usdt_price = (float) $item['price']; break; }
                }
            }
        }
        if ($usdt_price) {
            set_transient($cache_key, $usdt_price, 10);
            return $usdt_price;
        }
    } else {
        crypto_sekhyab_log('ERROR', 'Tetherland request failed', array('error' => $backup_response->get_error_message()));
    }

    // 3) CoinGecko USDT in IRR
    $cg_url = 'https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=irr,usd';
    $cg_res = wp_remote_get($cg_url, array('timeout' => 12));
    if (!is_wp_error($cg_res)) {
        $cg_body = wp_remote_retrieve_body($cg_res);
        $cg = json_decode($cg_body, true);
        if (isset($cg['tether']['irr'])) {
            $usdt_price = (float) $cg['tether']['irr'];
            set_transient($cache_key, $usdt_price, 10);
            crypto_sekhyab_log('INFO', 'USDT rate from CoinGecko simple price', array('rate' => $usdt_price));
            return $usdt_price;
        }
    } else {
        crypto_sekhyab_log('ERROR', 'CoinGecko simple price request failed', array('error' => $cg_res->get_error_message()));
    }

    // 4) ultimate fallback: theme option or safe default
    $fallback = (float) get_option('crypto_sekhyab_usd_to_irr', 1000000);
    crypto_sekhyab_log('WARNING', 'Using fallback USDT/IRR rate', array('rate' => $fallback));
    return $fallback;
}
/**
 * دریافت لیست ارزهای برتر از CoinGecko
 */
function crypto_sekhyab_get_top_cryptos($limit = 100, $page = 1) {
    $cache_key = 'top_cryptos_' . $limit . '_' . $page;
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $api_key = get_option('crypto_sekhyab_coingecko_api_key', '');
    $api_url = 'https://api.coingecko.com/api/v3/coins/markets';
    
    $params = array(
        'vs_currency' => 'usd',
        'order' => 'market_cap_desc',
        'per_page' => $limit,
        'page' => $page,
        'sparkline' => 'false',
        'price_change_percentage' => '24h,7d',
        'locale' => 'fa'
    );
    
    if (!empty($api_key)) {
        $params['x_cg_demo_api_key'] = $api_key;
    }
    
    $full_url = add_query_arg($params, $api_url);
    
    $response = wp_remote_get($full_url, array(
        'timeout' => 15,
        'headers' => array(
            'Accept' => 'application/json'
        )
    ));
    
    if (is_wp_error($response)) {
        return array();
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
if (!is_array($data)) {
        crypto_sekhyab_log('ERROR', 'CoinGecko markets returned invalid data');
        return array();
    }
    
    // ذخیره برای 2 دقیقه
    set_transient($cache_key, $data, 120);
    
    return $data;
}

/**
 * دریافت ترندینگ ارزها (محبوب‌ترین‌ها)
 */
function crypto_sekhyab_get_trending() {
    $cache_key = 'trending_cryptos';
    $cached = get_transient($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    $api_url = 'https://api.coingecko.com/api/v3/search/trending';
    
    $response = wp_remote_get($api_url, array('timeout' => 10));
    
if (is_wp_error($response)) {
        crypto_sekhyab_log('ERROR', 'CoinGecko trending request failed', array('error' => $response->get_error_message()));
        return array();
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
if (isset($data['coins'])) {
        crypto_sekhyab_log('INFO', 'Trending coins fetched', array('count' => count($data['coins'])));
        // ذخیره برای 30 دقیقه
        set_transient($cache_key, $data['coins'], 1800);
        return $data['coins'];
    }
    
    return array();
}

/**
 * فرمت کردن قیمت با تومان
 */
function crypto_sekhyab_format_price_irr($usd_price) {
    $usdt_rate = crypto_sekhyab_get_usdt_price();
    $irr_price = $usd_price * $usdt_rate;
    
    if ($irr_price >= 1000000) {
        return number_format($irr_price / 1000000, 2) . ' م';
    } elseif ($irr_price >= 1000) {
        return number_format($irr_price / 1000, 1) . ' ه';
    }
    
    return number_format($irr_price, 0);
}

/**
 * فرمت کردن قیمت دلاری
 */
function crypto_sekhyab_format_price_usd($price) {
    if ($price >= 1) {
        return '$' . number_format($price, 2);
    } elseif ($price >= 0.01) {
        return '$' . number_format($price, 4);
    } else {
        return '$' . number_format($price, 6);
    }
}

/**
 * AJAX: دریافت لیست ارزها
 */
function crypto_sekhyab_ajax_get_cryptos() {
    check_ajax_referer('crypto_sekhyab_nonce', 'nonce');
    
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    $cryptos = crypto_sekhyab_get_top_cryptos($limit, $page);
    $usdt_rate = crypto_sekhyab_get_usdt_price();
    
    if (empty($cryptos)) {
        wp_send_json_error('خطا در دریافت اطلاعات');
        return;
    }
    
crypto_sekhyab_log('INFO', 'AJAX get_cryptos served', array('count' => count($cryptos), 'usdt_rate' => $usdt_rate));
    wp_send_json_success(array(
        'cryptos' => $cryptos,
        'usdt_rate' => $usdt_rate
    ));
}
add_action('wp_ajax_get_cryptos', 'crypto_sekhyab_ajax_get_cryptos');
add_action('wp_ajax_nopriv_get_cryptos', 'crypto_sekhyab_ajax_get_cryptos');

/**
 * AJAX: دریافت نرخ تتر
 */
function crypto_sekhyab_ajax_get_usdt_rate() {
    check_ajax_referer('crypto_sekhyab_nonce', 'nonce');
    
    $usdt_rate = crypto_sekhyab_get_usdt_price();
    
crypto_sekhyab_log('DEBUG', 'AJAX get_usdt_rate served', array('rate' => $usdt_rate));
    wp_send_json_success(array(
        'rate' => $usdt_rate,
        'formatted' => number_format($usdt_rate, 0) . ' تومان'
    ));
}
add_action('wp_ajax_get_usdt_rate', 'crypto_sekhyab_ajax_get_usdt_rate');
add_action('wp_ajax_nopriv_get_usdt_rate', 'crypto_sekhyab_ajax_get_usdt_rate');

/**
 * AJAX: Proxy for CoinGecko market_chart to bypass client-side blocks
 */
function crypto_sekhyab_ajax_get_market_chart() {
    check_ajax_referer('crypto_sekhyab_nonce', 'nonce');

    $coin_id = isset($_POST['coin_id']) ? sanitize_text_field($_POST['coin_id']) : '';
    $days    = isset($_POST['days']) ? sanitize_text_field($_POST['days']) : '7';

    if (empty($coin_id)) {
        wp_send_json_error('coin_id is required');
    }

    $cache_key = 'cg_chart_' . $coin_id . '_' . $days;
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
    }

    $args = array(
        'vs_currency' => 'usd',
        'days'        => $days,
    );
    $url = add_query_arg($args, 'https://api.coingecko.com/api/v3/coins/' . rawurlencode($coin_id) . '/market_chart');

    $resp = wp_remote_get($url, array('timeout' => 20, 'headers' => array('Accept' => 'application/json')));
    if (is_wp_error($resp)) {
        wp_send_json_error($resp->get_error_message());
    }
    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (!is_array($body)) {
        wp_send_json_error('Invalid response');
    }
    set_transient($cache_key, $body, 300); // 5 minutes
    wp_send_json_success($body);
}
add_action('wp_ajax_get_market_chart', 'crypto_sekhyab_ajax_get_market_chart');
add_action('wp_ajax_nopriv_get_market_chart', 'crypto_sekhyab_ajax_get_market_chart');

/**
 * AJAX: Lightweight OHLC/series proxy (uses market_chart for simplicity)
 */
function crypto_sekhyab_ajax_get_series() {
    check_ajax_referer('crypto_sekhyab_nonce', 'nonce');
    $coin_id = isset($_POST['coin_id']) ? sanitize_text_field($_POST['coin_id']) : '';
    $days    = isset($_POST['days']) ? sanitize_text_field($_POST['days']) : '365';
    if (empty($coin_id)) {
        wp_send_json_error('coin_id is required');
    }
    $cache_key = 'cg_series_' . $coin_id . '_' . $days;
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
    }
    $url = add_query_arg(array('vs_currency' => 'usd', 'days' => $days), 'https://api.coingecko.com/api/v3/coins/' . rawurlencode($coin_id) . '/market_chart');
    $resp = wp_remote_get($url, array('timeout' => 20));
    if (is_wp_error($resp)) {
        wp_send_json_error($resp->get_error_message());
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    if (!isset($data['prices'])) {
        wp_send_json_error('No prices');
    }
    $series = array();
    foreach ($data['prices'] as $row) {
        $series[] = array('time' => intval($row[0] / 1000), 'value' => (float)$row[1]);
    }
    set_transient($cache_key, array('series' => $series), 300);
    wp_send_json_success(array('series' => $series));
}
add_action('wp_ajax_get_series', 'crypto_sekhyab_ajax_get_series');
add_action('wp_ajax_nopriv_get_series', 'crypto_sekhyab_ajax_get_series');

/**
 * شورت‌کد لیست ارزها با دریافت خودکار
 */
function crypto_sekhyab_auto_crypto_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 50,
        'show_rank' => 'true',
        'show_chart' => 'false',
        'currency' => 'both'
    ), $atts);
    
    $cryptos = crypto_sekhyab_get_top_cryptos($atts['limit']);
    $usdt_rate = crypto_sekhyab_get_usdt_price();
    
    if (empty($cryptos)) {
        return '<p>در حال بارگذاری...</p>';
    }
    
    ob_start();
    ?>
    <div class="crypto-modern-table-wrapper" data-currency="<?php echo esc_attr($atts['currency']); ?>">
        <div class="crypto-table-header">
            <h2 class="table-title">قیمت لحظه‌ای ارزهای دیجیتال</h2>
            <div class="usdt-rate-display">
                نرخ تتر: <span class="usdt-value"><?php echo number_format($usdt_rate, 0); ?></span> تومان
            </div>
        </div>
        
        <div class="crypto-table-container">
            <table class="crypto-modern-table">
                <thead>
                    <tr>
                        <?php if ($atts['show_rank'] === 'true') : ?>
                            <th class="rank-col">#</th>
                        <?php endif; ?>
                        <th class="name-col">نام</th>
                        <th class="price-col">قیمت</th>
                        <th class="change-col">24h</th>
                        <th class="change-col">7d</th>
                        <th class="volume-col">حجم 24h</th>
                        <th class="marketcap-col">مارکت کپ</th>
                        <?php if ($atts['show_chart'] === 'true') : ?>
                            <th class="chart-col">نمودار 7 روز</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cryptos as $crypto) : 
                        $price_irr = $crypto['current_price'] * $usdt_rate;
                        $change_24h = isset($crypto['price_change_percentage_24h']) ? $crypto['price_change_percentage_24h'] : 0;
                        $change_7d = isset($crypto['price_change_percentage_7d_in_currency']) ? $crypto['price_change_percentage_7d_in_currency'] : 0;
                    ?>
                        <tr class="crypto-row" data-id="<?php echo esc_attr($crypto['id']); ?>">
                            <?php if ($atts['show_rank'] === 'true') : ?>
                                <td class="rank-col">
                                    <span class="rank-badge"><?php echo esc_html($crypto['market_cap_rank']); ?></span>
                                </td>
                            <?php endif; ?>
                            
                            <td class="name-col">
                                <div class="crypto-name-wrapper">
                                    <img src="<?php echo esc_url($crypto['image']); ?>" alt="<?php echo esc_attr($crypto['name']); ?>" class="crypto-icon">
                                    <div class="crypto-name-text">
                                        <span class="crypto-name"><?php echo esc_html($crypto['name']); ?></span>
                                        <span class="crypto-symbol"><?php echo esc_html(strtoupper($crypto['symbol'])); ?></span>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="price-col">
                                <div class="price-wrapper">
                                    <span class="price-usd"><?php echo crypto_sekhyab_format_price_usd($crypto['current_price']); ?></span>
                                    <span class="price-irr"><?php echo number_format($price_irr, 0); ?> ت</span>
                                </div>
                            </td>
                            
                            <td class="change-col">
                                <span class="change-badge <?php echo $change_24h >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $change_24h >= 0 ? '▲' : '▼'; ?>
                                    <?php echo number_format(abs($change_24h), 2); ?>%
                                </span>
                            </td>
                            
                            <td class="change-col">
                                <span class="change-badge <?php echo $change_7d >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $change_7d >= 0 ? '▲' : '▼'; ?>
                                    <?php echo number_format(abs($change_7d), 2); ?>%
                                </span>
                            </td>
                            
                            <td class="volume-col">
                                $<?php echo number_format($crypto['total_volume'] / 1000000, 1); ?>M
                            </td>
                            
                            <td class="marketcap-col">
                                $<?php echo number_format($crypto['market_cap'] / 1000000000, 2); ?>B
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('crypto_auto_list', 'crypto_sekhyab_auto_crypto_list_shortcode');

// duplicate-safe: get_market_chart already declared above
