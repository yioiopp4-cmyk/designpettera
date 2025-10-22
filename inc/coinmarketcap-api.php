<?php
/**
 * CoinMarketCap API Handler - رایگان و قدرتمند!
 *
 * @package CryptoSekhyab
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * کلاس مدیریت CoinMarketCap API
 */
class CoinMarketCap_API {
    
    private $api_key;
    private $base_url = 'https://pro-api.coinmarketcap.com';
    private $cache_time = 300; // 5 دقیقه
    
    public function __construct() {
        // دریافت API Key از تنظیمات
        $this->api_key = get_option('cmc_api_key', 'c53a3219-e9f0-47aa-8508-d4cbafb591af');
    }
    
    /**
     * درخواست به API
     */
    private function request($endpoint, $params = array()) {
        $url = $this->base_url . $endpoint;
        
        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'X-CMC_PRO_API_KEY' => $this->api_key,
                'Accept' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            crypto_sekhyab_log('ERROR', 'CMC API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['status']['error_code']) && $data['status']['error_code'] != 0) {
            crypto_sekhyab_log('ERROR', 'CMC API Error: ' . $data['status']['error_message']);
            return false;
        }
        
        return $data['data'] ?? false;
    }
    
    /**
     * دریافت لیست ارزها با pagination
     */
    public function get_listings($start = 1, $limit = 100) {
        $cache_key = "cmc_listings_{$start}_{$limit}";
        
        // چک کردن Cache
        $cached = $this->get_from_file($cache_key);
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $data = $this->request('/v1/cryptocurrency/listings/latest', array(
            'start' => $start,
            'limit' => $limit,
            'convert' => 'USD'
        ));
        
        if ($data) {
            $this->save_to_file($cache_key, $data, $this->cache_time);
        }
        
        return $data;
    }
    
    /**
     * دریافت اطلاعات تک ارز
     */
    public function get_coin_info($coin_id) {
        $cache_key = "cmc_info_{$coin_id}";
        
        // چک کردن Cache
        $cached = $this->get_from_file($cache_key);
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $data = $this->request('/v2/cryptocurrency/info', array(
            'id' => $coin_id
        ));
        
        if ($data) {
            $this->save_to_file($cache_key, $data, 3600); // 1 ساعت
        }
        
        return $data;
    }
    
    /**
     * دریافت قیمت لحظه‌ای چند ارز
     */
    public function get_quotes($coin_ids) {
        if (is_array($coin_ids)) {
            $coin_ids = implode(',', $coin_ids);
        }
        
        $cache_key = "cmc_quotes_" . md5($coin_ids);
        
        // Cache کوتاه‌تر برای قیمت‌ها
        $cached = $this->get_from_file($cache_key);
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $data = $this->request('/v1/cryptocurrency/quotes/latest', array(
            'id' => $coin_ids,
            'convert' => 'USD'
        ));
        
        if ($data) {
            $this->save_to_file($cache_key, $data, 60); // 1 دقیقه
        }
        
        return $data;
    }
    
    /**
     * دریافت آمار کلی بازار
     */
    public function get_global_metrics() {
        $cache_key = 'cmc_global_metrics';
        
        $cached = $this->get_from_file($cache_key);
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        $data = $this->request('/v1/global-metrics/quotes/latest');
        
        if ($data) {
            $this->save_to_file($cache_key, $data, 300); // 5 دقیقه
        }
        
        return $data;
    }
    
    /**
     * دریافت ارزهای ترند (Top Gainers)
     */
    public function get_trending() {
        // CMC نداره trending، پس top gainers 24h رو برمی‌گردونیم
        $cache_key = 'cmc_trending';
        
        $cached = $this->get_from_file($cache_key);
        if ($cached !== false && get_option('crypto_sekhyab_enable_cache', '1') == '1') {
            return $cached;
        }
        
        // دریافت 50 ارز اول و sort بر اساس تغییرات
        $data = $this->get_listings(1, 50);
        
        if ($data) {
            // Sort by 24h change
            usort($data, function($a, $b) {
                $change_a = $a['quote']['USD']['percent_change_24h'] ?? 0;
                $change_b = $b['quote']['USD']['percent_change_24h'] ?? 0;
                return $change_b <=> $change_a;
            });
            
            $trending = array_slice($data, 0, 10);
            $this->save_to_file($cache_key, $trending, 600); // 10 دقیقه
            return $trending;
        }
        
        return false;
    }
    
    /**
     * ایجاد صفحه برای ارز
     */
    public function create_or_update_coin($coin) {
        if (!isset($coin['id'])) {
            return false;
        }
        
        $cmc_id = $coin['id'];
        $name = $coin['name'] ?? 'Crypto';
        $symbol = $coin['symbol'] ?? 'CRYPTO';
        $slug = $coin['slug'] ?? sanitize_title($name);
        
        // چک کردن وجود
        $existing = get_posts(array(
            'post_type' => 'cryptocurrency',
            'meta_key' => '_crypto_cmc_id',
            'meta_value' => $cmc_id,
            'posts_per_page' => 1,
            'post_status' => 'any'
        ));
        
        $post_data = array(
            'post_type' => 'cryptocurrency',
            'post_title' => $name . ' (' . strtoupper($symbol) . ')',
            'post_name' => $slug,
            'post_content' => $this->generate_content($coin),
            'post_status' => 'publish',
            'post_author' => 1
        );
        
        if (!empty($existing)) {
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post($post_data);
            $action = 'updated';
        } else {
            $post_id = wp_insert_post($post_data);
            $action = 'created';
        }
        
        if (is_wp_error($post_id) || !$post_id) {
            return false;
        }
        
        // ذخیره متادیتا
        update_post_meta($post_id, '_crypto_cmc_id', $cmc_id);
        update_post_meta($post_id, '_crypto_symbol', strtoupper($symbol));
        update_post_meta($post_id, '_crypto_slug', $slug);
        update_post_meta($post_id, '_crypto_rank', $coin['cmc_rank'] ?? 0);
        
        // قیمت و آمار
        if (isset($coin['quote']['USD'])) {
            $quote = $coin['quote']['USD'];
            update_post_meta($post_id, '_crypto_price_usd', $quote['price'] ?? 0);
            update_post_meta($post_id, '_crypto_market_cap', $quote['market_cap'] ?? 0);
            update_post_meta($post_id, '_crypto_volume_24h', $quote['volume_24h'] ?? 0);
            update_post_meta($post_id, '_crypto_change_24h', $quote['percent_change_24h'] ?? 0);
            update_post_meta($post_id, '_crypto_change_7d', $quote['percent_change_7d'] ?? 0);
        }
        
        // نماد TradingView
        $tv_symbol = $this->get_tradingview_symbol($symbol);
        update_post_meta($post_id, '_crypto_tradingview_symbol', $tv_symbol);
        
        // لوگو (اگر داریم)
        if (isset($coin['logo'])) {
            update_post_meta($post_id, '_crypto_logo_url', $coin['logo']);
        }
        
        return $action;
    }
    
    /**
     * ایجاد Batch ارزها
     */
    public function create_batch($start = 1, $end = 500, $batch_size = 100) {
        $created = 0;
        $updated = 0;
        $errors = 0;
        
        for ($i = $start; $i <= $end; $i += $batch_size) {
            $limit = min($batch_size, $end - $i + 1);
            
            $coins = $this->get_listings($i, $limit);
            
            if (!$coins) {
                $errors++;
                continue;
            }
            
            foreach ($coins as $coin) {
                $result = $this->create_or_update_coin($coin);
                
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                } else {
                    $errors++;
                }
            }
            
            // صبر بین batch ها
            sleep(2);
        }
        
        crypto_sekhyab_log('SUCCESS', "CMC Batch: Created=$created, Updated=$updated, Errors=$errors");
        
        return array(
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors
        );
    }
    
    /**
     * تولید محتوا
     */
    private function generate_content($coin) {
        $name = $coin['name'] ?? 'ارز دیجیتال';
        $symbol = strtoupper($coin['symbol'] ?? '');
        $rank = $coin['cmc_rank'] ?? 'نامشخص';
        
        $content = "<h2>🪙 معرفی $name ($symbol)</h2>\n\n";
        $content .= "<p><strong>$name</strong> با نماد <strong>$symbol</strong> یکی از ارزهای دیجیتال است که در رتبه <strong>$rank</strong> بازار جهانی قرار دارد.</p>\n\n";
        
        $content .= "<h3>💰 قیمت و تحلیل $name</h3>\n";
        $content .= "<p>در این صفحه می‌توانید قیمت لحظه‌ای $name به تومان و دلار، نمودار تعاملی قیمت، و تمامی آمار و اطلاعات مربوط به $name را مشاهده کنید.</p>\n\n";
        
        $content .= "<h3>📊 ویژگی‌های $name</h3>\n";
        $content .= "<ul>\n";
        $content .= "<li><strong>نماد:</strong> $symbol</li>\n";
        $content .= "<li><strong>رتبه بازار:</strong> #{$rank}</li>\n";
        $content .= "<li><strong>منبع داده:</strong> CoinMarketCap</li>\n";
        $content .= "<li><strong>بروزرسانی:</strong> لحظه‌ای</li>\n";
        $content .= "</ul>\n\n";
        
        $content .= "<h3>📈 نمودار و تحلیل تکنیکال</h3>\n";
        $content .= "<p>نمودار TradingView در بالای این صفحه، ابزارهای تحلیل تکنیکال حرفه‌ای را در اختیار شما قرار می‌دهد. می‌توانید تایم‌فریم‌های مختلف، اندیکاتورهای متنوع و ابزارهای رسم را به صورت رایگان استفاده کنید.</p>\n\n";
        
        $content .= "<blockquote><p>💡 <strong>نکته مهم:</strong> قیمت‌های این صفحه هر 30 ثانیه به صورت خودکار بروزرسانی می‌شوند و از API رسمی CoinMarketCap دریافت می‌گردند.</p></blockquote>";
        
        return $content;
    }
    
    /**
     * تبدیل symbol به TradingView
     */
    private function get_tradingview_symbol($symbol) {
        $symbol = strtoupper($symbol);
        
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
            'MATIC' => 'BINANCE:MATICUSDT',
            'LTC' => 'BINANCE:LTCUSDT',
            'AVAX' => 'BINANCE:AVAXUSDT',
            'LINK' => 'BINANCE:LINKUSDT',
            'UNI' => 'BINANCE:UNIUSDT'
        );
        
        return isset($mapping[$symbol]) ? $mapping[$symbol] : "BINANCE:{$symbol}USDT";
    }
    
    /**
     * ذخیره در فایل
     */
    private function save_to_file($filename, $data, $expire = 3600) {
        $cache_dir = WP_CONTENT_DIR . '/uploads/cmc-cache';
        
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
     * خواندن از فایل
     */
    private function get_from_file($filename) {
        $cache_file = WP_CONTENT_DIR . '/uploads/cmc-cache/' . $filename . '.json';
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $cache_data = json_decode(file_get_contents($cache_file), true);
        
        if (!$cache_data || !isset($cache_data['expire']) || !isset($cache_data['data'])) {
            return false;
        }
        
        if (time() > $cache_data['expire']) {
            unlink($cache_file);
            return false;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * تبدیل داده CMC به فرمت استاندارد
     */
    public function normalize_coin_data($cmc_coin) {
        if (!is_array($cmc_coin) || !isset($cmc_coin['quote']['USD'])) {
            return null;
        }
        
        $quote = $cmc_coin['quote']['USD'];
        
        return array(
            'id' => $cmc_coin['slug'] ?? '',
            'cmc_id' => $cmc_coin['id'],
            'symbol' => $cmc_coin['symbol'] ?? '',
            'name' => $cmc_coin['name'] ?? '',
            'image' => '', // CMC لوگو در endpoint دیگه است
            'current_price' => $quote['price'] ?? 0,
            'market_cap' => $quote['market_cap'] ?? 0,
            'market_cap_rank' => $cmc_coin['cmc_rank'] ?? 0,
            'total_volume' => $quote['volume_24h'] ?? 0,
            'price_change_percentage_1h' => $quote['percent_change_1h'] ?? 0,
            'price_change_percentage_24h' => $quote['percent_change_24h'] ?? 0,
            'price_change_percentage_7d' => $quote['percent_change_7d'] ?? 0,
            'circulating_supply' => $cmc_coin['circulating_supply'] ?? 0,
            'total_supply' => $cmc_coin['total_supply'] ?? 0,
            'max_supply' => $cmc_coin['max_supply'] ?? 0
        );
    }
    
    /**
     * دریافت لوگوها
     */
    public function get_logo_url($cmc_id) {
        return "https://s2.coinmarketcap.com/static/img/coins/64x64/{$cmc_id}.png";
    }
}

/**
 * Helper Function
 */
function cmc_api() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CoinMarketCap_API();
    }
    return $instance;
}
