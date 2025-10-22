<?php
/**
 * CoinGecko API Handler - دریافت هزاران ارز
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * کلاس مدیریت API CoinGecko
 */
class CoinGecko_API_Handler {
    
    private $base_url = 'https://api.coingecko.com/api/v3';
    private $cache_time = 300; // 5 دقیقه
    
    /**
     * ذخیره داده در فایل (برای داده‌های بزرگ)
     */
    private function save_to_file($filename, $data, $expire = 3600) {
        $cache_dir = WP_CONTENT_DIR . '/uploads/cg-cache';
        
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        
        $cache_file = $cache_dir . '/' . $filename . '.json';
        
        $cache_data = array(
            'expire' => time() + $expire,
            'data' => $data
        );
        
        file_put_contents($cache_file, json_encode($cache_data));
    }
    
    /**
     * خواندن داده از فایل
     */
    private function get_from_file($filename) {
        $cache_file = WP_CONTENT_DIR . '/uploads/cg-cache/' . $filename . '.json';
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $cache_data = json_decode(file_get_contents($cache_file), true);
        
        if (!$cache_data || !isset($cache_data['expire']) || !isset($cache_data['data'])) {
            return false;
        }
        
        // چک کردن انقضا
        if (time() > $cache_data['expire']) {
            unlink($cache_file);
            return false;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * دریافت لیست تمام ارزها (فقط ID و نام)
     * ذخیره در فایل JSON به جای Database
     */
    public function get_all_coins_list() {
        // چک کردن Cache فایل
        $cached = $this->get_from_file('all_coins_list');
        if ($cached !== false) {
            return $cached;
        }
        
        // دریافت از API
        $response = wp_remote_get($this->base_url . '/coins/list', array(
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            crypto_sekhyab_log('ERROR', 'Failed to fetch coins list: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data)) {
            // ذخیره در فایل (24 ساعت)
            $this->save_to_file('all_coins_list', $data, 86400);
            return $data;
        }
        
        return false;
    }
    
    /**
     * دریافت تعداد کل ارزها
     */
    public function get_total_coins_count() {
        $list = $this->get_all_coins_list();
        return $list ? count($list) : 0;
    }
    
    /**
     * دریافت ارزها با pagination
     */
    public function get_coins_paginated($page = 1, $per_page = 100) {
        $cache_key = "cg_coins_page_{$page}_{$per_page}";
        $cached = get_transient($cache_key);
        
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $url = add_query_arg(array(
            'vs_currency' => 'usd',
            'order' => 'market_cap_desc',
            'per_page' => $per_page,
            'page' => $page,
            'sparkline' => 'true',
            'price_change_percentage' => '1h,24h,7d'
        ), $this->base_url . '/coins/markets');
        
        $response = wp_remote_get($url, array('timeout' => 20));
        
        if (is_wp_error($response)) {
            crypto_sekhyab_log('ERROR', 'Failed to fetch page ' . $page . ': ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data)) {
            set_transient($cache_key, $data, $this->cache_time);
            return $data;
        }
        
        return false;
    }
    
    /**
     * دریافت اطلاعات تک ارز با جزئیات کامل
     */
    public function get_coin_details($coin_id) {
        $cache_key = "cg_coin_details_{$coin_id}";
        $cached = get_transient($cache_key);
        
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $url = add_query_arg(array(
            'localization' => 'false',
            'tickers' => 'false',
            'community_data' => 'true',
            'developer_data' => 'true',
            'sparkline' => 'true'
        ), $this->base_url . '/coins/' . $coin_id);
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data)) {
            set_transient($cache_key, $data, 600); // 10 دقیقه
            return $data;
        }
        
        return false;
    }

    /**
     * دریافت تیکرهای صرافی برای یک ارز (Coin tickers)
     * منبع: /coins/{id}/tickers
     *
     * برای جدول بازارها استفاده می‌شود و 5 دقیقه Cache می‌شود
     */
    public function get_coin_tickers($coin_id, $page = 1) {
        if (empty($coin_id)) {
            return array();
        }

        $page = max(1, intval($page));
        $cache_key = "cg_coin_tickers_{$coin_id}_{$page}";
        $cached = get_transient($cache_key);

        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }

        $url = add_query_arg(array(
            'include_exchange_logo' => 'true',
            'page' => $page,
        ), $this->base_url . '/coins/' . rawurlencode($coin_id) . '/tickers');

        $response = wp_remote_get($url, array('timeout' => 25));

        if (is_wp_error($response)) {
            crypto_sekhyab_log('ERROR', 'Failed to fetch coin tickers', array('coin_id' => $coin_id, 'page' => $page, 'error' => $response->get_error_message()));
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $tickers = isset($data['tickers']) && is_array($data['tickers']) ? $data['tickers'] : array();

        // Cache برای 5 دقیقه
        set_transient($cache_key, $tickers, 300);

        return $tickers;
    }

    /**
     * دریافت بروزرسانی‌ها و اخبار مرتبط با ارز (Status Updates)
     * منبع: /coins/{id}/status_updates
     */
    public function get_coin_status_updates($coin_id, $page = 1, $per_page = 20) {
        if (empty($coin_id)) {
            return array();
        }

        $page = max(1, intval($page));
        $per_page = min(50, max(1, intval($per_page)));
        $cache_key = "cg_coin_status_{$coin_id}_{$page}_{$per_page}";
        $cached = get_transient($cache_key);

        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }

        $url = add_query_arg(array(
            'page' => $page,
            'per_page' => $per_page,
        ), $this->base_url . '/coins/' . rawurlencode($coin_id) . '/status_updates');

        $response = wp_remote_get($url, array('timeout' => 20));
        if (is_wp_error($response)) {
            crypto_sekhyab_log('ERROR', 'Failed to fetch status updates', array('coin_id' => $coin_id, 'error' => $response->get_error_message()));
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $updates = isset($data['status_updates']) && is_array($data['status_updates']) ? $data['status_updates'] : array();

        set_transient($cache_key, $updates, 600); // 10 دقیقه
        return $updates;
    }
    
    /**
     * دریافت آمار کلی بازار
     */
    public function get_global_market_data() {
        $cache_key = 'cg_global_market_data';
        $cached = get_transient($cache_key);
        
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $response = wp_remote_get($this->base_url . '/global', array('timeout' => 20));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data['data'])) {
            set_transient($cache_key, $data['data'], 300); // 5 دقیقه
            return $data['data'];
        }
        
        return false;
    }
    
    /**
     * دریافت ارزهای ترند
     */
    public function get_trending_coins() {
        $cache_key = 'cg_trending_coins';
        $cached = get_transient($cache_key);
        
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $response = wp_remote_get($this->base_url . '/search/trending', array('timeout' => 20));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data['coins'])) {
            set_transient($cache_key, $data['coins'], 600); // 10 دقیقه
            return $data['coins'];
        }
        
        return false;
    }
    
    /**
     * ایجاد batch ارزها (برای ایجاد خودکار صفحات)
     */
    public function create_coins_batch($start_page = 1, $end_page = 5) {
        $created = 0;
        $updated = 0;
        $errors = 0;
        
        for ($page = $start_page; $page <= $end_page; $page++) {
            $coins = $this->get_coins_paginated($page, 100);
            
            if (!$coins) {
                $errors++;
                continue;
            }
            
            foreach ($coins as $coin) {
                $result = $this->create_or_update_coin_post($coin);
                
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                } else {
                    $errors++;
                }
                
                // کمی صبر کنیم برای جلوگیری از فشار به سرور
                usleep(100000); // 0.1 ثانیه
            }
            
            // بین هر صفحه 2 ثانیه صبر کنیم
            sleep(2);
        }
        
        crypto_sekhyab_log('SUCCESS', "Batch complete: Created=$created, Updated=$updated, Errors=$errors");
        
        return array(
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
            'total' => $created + $updated
        );
    }
    
    /**
     * ایجاد یا بروزرسانی پست ارز
     */
    private function create_or_update_coin_post($coin) {
        if (!isset($coin['id'])) {
            return false;
        }
        
        // چک کردن وجود پست
        $existing = get_posts(array(
            'post_type' => 'cryptocurrency',
            'meta_key' => '_crypto_coingecko_id',
            'meta_value' => $coin['id'],
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));
        
        $post_data = array(
            'post_type' => 'cryptocurrency',
            'post_title' => $coin['name'] . ' (' . strtoupper($coin['symbol']) . ')',
            'post_content' => $this->generate_coin_content($coin),
            'post_status' => 'publish',
            'post_author' => 1
        );
        
        if (!empty($existing)) {
            // بروزرسانی
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($post_data);
            $action = 'updated';
        } else {
            // ایجاد جدید
            $post_id = wp_insert_post($post_data);
            $action = 'created';
        }
        
        if (is_wp_error($post_id) || !$post_id) {
            return false;
        }
        
        // ذخیره متادیتا
        $this->update_coin_meta($post_id, $coin);
        
        return $action;
    }
    
    /**
     * بروزرسانی متادیتای ارز
     */
    private function update_coin_meta($post_id, $coin) {
        update_post_meta($post_id, '_crypto_coingecko_id', $coin['id']);
        update_post_meta($post_id, '_crypto_symbol', strtoupper($coin['symbol']));
        update_post_meta($post_id, '_crypto_rank', $coin['market_cap_rank'] ?? 0);
        update_post_meta($post_id, '_crypto_image', $coin['image'] ?? '');
        update_post_meta($post_id, '_crypto_current_price', $coin['current_price'] ?? 0);
        update_post_meta($post_id, '_crypto_market_cap', $coin['market_cap'] ?? 0);
        update_post_meta($post_id, '_crypto_total_volume', $coin['total_volume'] ?? 0);
        update_post_meta($post_id, '_crypto_price_change_24h', $coin['price_change_percentage_24h'] ?? 0);
        update_post_meta($post_id, '_crypto_ath', $coin['ath'] ?? 0);
        update_post_meta($post_id, '_crypto_atl', $coin['atl'] ?? 0);
        
        // نماد TradingView
        $tv_symbol = $this->get_tradingview_symbol($coin['symbol']);
        update_post_meta($post_id, '_crypto_tradingview_symbol', $tv_symbol);
        
        // Sparkline
        if (isset($coin['sparkline_in_7d']['price'])) {
            update_post_meta($post_id, '_crypto_sparkline', json_encode($coin['sparkline_in_7d']['price']));
        }
    }
    
    /**
     * تولید محتوای خودکار
     */
    private function generate_coin_content($coin) {
        // چک کردن $coin
        if (!is_array($coin)) {
            $coin = array();
        }
        
        $name = isset($coin['name']) ? $coin['name'] : 'ارز دیجیتال';
        $symbol = isset($coin['symbol']) ? strtoupper($coin['symbol']) : 'CRYPTO';
        $rank = isset($coin['market_cap_rank']) ? $coin['market_cap_rank'] : 'نامشخص';
        
        $content = "<h2>🪙 درباره $name ($symbol)</h2>\n\n";
        $content .= "<p><strong>$name</strong> یکی از ارزهای دیجیتال است که با نماد <strong>$symbol</strong> شناخته می‌شود و در رتبه <strong>$rank</strong> بازار قرار دارد.</p>\n\n";
        
        $content .= "<h3>💰 قیمت و بازار $name</h3>\n";
        $content .= "<p>در این صفحه می‌توانید قیمت لحظه‌ای $name، نمودار تعاملی قیمت، و تمامی آمار مربوط به این ارز دیجیتال را مشاهده کنید. قیمت $name به صورت real-time بروزرسانی می‌شود.</p>\n\n";
        
        $content .= "<h3>📊 ویژگی‌های $name</h3>\n";
        $content .= "<ul>\n";
        $content .= "<li>نماد معاملاتی: <strong>$symbol</strong></li>\n";
        $content .= "<li>رتبه بازار: <strong>#{$rank}</strong></li>\n";
        $content .= "<li>نمودار قیمت تعاملی با TradingView</li>\n";
        $content .= "<li>قیمت لحظه‌ای به تومان و دلار</li>\n";
        $content .= "<li>تاریخچه کامل تغییرات قیمت</li>\n";
        $content .= "<li>حجم معاملات و Market Cap</li>\n";
        $content .= "</ul>\n\n";
        
        $content .= "<h3>📈 تحلیل و پیش‌بینی قیمت</h3>\n";
        $content .= "<p>برای تحلیل دقیق قیمت $name می‌توانید از نمودار TradingView در بالای این صفحه استفاده کنید. این نمودار به شما امکان می‌دهد تایم‌فریم‌های مختلف، اندیکاتورها و ابزارهای رسم را به صورت رایگان استفاده کنید.</p>\n\n";
        
        $content .= "<blockquote><p>💡 <strong>نکته:</strong> قیمت‌های نمایش داده شده در این صفحه به صورت خودکار و لحظه‌ای از منابع معتبر دریافت می‌شوند.</p></blockquote>";
        
        return $content;
    }
    
    /**
     * تبدیل symbol به TradingView
     */
    private function get_tradingview_symbol($symbol) {
        $symbol = strtoupper($symbol);
        
        $common = array(
            'BTC' => 'BINANCE:BTCUSDT',
            'ETH' => 'BINANCE:ETHUSDT',
            'BNB' => 'BINANCE:BNBUSDT',
            'XRP' => 'BINANCE:XRPUSDT',
            'ADA' => 'BINANCE:ADAUSDT',
            'DOGE' => 'BINANCE:DOGEUSDT',
            'SOL' => 'BINANCE:SOLUSDT',
            'DOT' => 'BINANCE:DOTUSDT',
            'MATIC' => 'BINANCE:MATICUSDT',
            'LTC' => 'BINANCE:LTCUSDT'
        );
        
        return isset($common[$symbol]) ? $common[$symbol] : "BINANCE:{$symbol}USDT";
    }
}

// ایجاد instance
function cg_api() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CoinGecko_API_Handler();
    }
    return $instance;
}
