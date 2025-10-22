<?php
/**
 * صفحه تک ارز - UI عالی
 */

get_header('arzdigital');

$coingecko_id = get_post_meta(get_the_ID(), '_crypto_coingecko_id', true);
$symbol = get_post_meta(get_the_ID(), '_crypto_symbol', true);
$rank = get_post_meta(get_the_ID(), '_crypto_rank', true);

// نماد TradingView با fallback امن
$tv_symbol = get_post_meta(get_the_ID(), '_crypto_tradingview_symbol', true);
if (empty($tv_symbol) && !empty($symbol)) {
    $tv_symbol = 'BINANCE:' . strtoupper($symbol) . 'USDT';
}

// اگر coingecko_id خالی است، سعی کنیم از نام پست پیدا کنیم
if (empty($coingecko_id)) {
    $title = get_the_title();
    // استخراج symbol از عنوان (format: "Bitcoin (BTC)")
    if (preg_match('/\(([A-Z]+)\)/', $title, $matches)) {
        $symbol = $matches[1];
    }
    // تلاش برای یافتن coingecko_id
    $coingecko_id = strtolower(str_replace(array('(', ')', ' '), array('', '', '-'), explode('(', $title)[0]));
}

$usdt_rate = crypto_sekhyab_get_usdt_price();

// دریافت داده لحظه‌ای
$api = cg_api();
$coin_data = null;
if (!empty($coingecko_id)) {
    $coin_data = $api->get_coin_details($coingecko_id);
}

$price_usd = 0;
$price_irr = 0;
$change_24h = 0;
$change_7d = 0;
$change_30d = 0;
$volume_24h = 0;
$market_cap = 0;
$circulating_supply = 0;
$total_supply = 0;
$max_supply = 0;
$ath = 0;
$ath_change = 0;
$atl = 0;
$atl_change = 0;
$high_24h = 0;
$low_24h = 0;

if ($coin_data && is_array($coin_data) && isset($coin_data['market_data'])) {
    $md = $coin_data['market_data'];
    $price_usd = isset($md['current_price']['usd']) ? $md['current_price']['usd'] : 0;
    $price_irr = $price_usd * $usdt_rate;
    $change_24h = isset($md['price_change_percentage_24h']) ? $md['price_change_percentage_24h'] : 0;
    $change_7d = isset($md['price_change_percentage_7d']) ? $md['price_change_percentage_7d'] : 0;
    $change_30d = isset($md['price_change_percentage_30d']) ? $md['price_change_percentage_30d'] : 0;
    $volume_24h = isset($md['total_volume']['usd']) ? $md['total_volume']['usd'] : 0;
    $market_cap = isset($md['market_cap']['usd']) ? $md['market_cap']['usd'] : 0;
    $circulating_supply = isset($md['circulating_supply']) ? $md['circulating_supply'] : 0;
    $total_supply = isset($md['total_supply']) ? $md['total_supply'] : 0;
    $max_supply = isset($md['max_supply']) ? $md['max_supply'] : 0;
    $ath = isset($md['ath']['usd']) ? $md['ath']['usd'] : 0;
    $ath_change = isset($md['ath_change_percentage']['usd']) ? $md['ath_change_percentage']['usd'] : 0;
    $atl = isset($md['atl']['usd']) ? $md['atl']['usd'] : 0;
    $atl_change = isset($md['atl_change_percentage']['usd']) ? $md['atl_change_percentage']['usd'] : 0;
    $high_24h = isset($md['high_24h']['usd']) ? $md['high_24h']['usd'] : 0;
    $low_24h = isset($md['low_24h']['usd']) ? $md['low_24h']['usd'] : 0;
}
// داده‌های تکمیلی برای UI جدید
$coin_name = get_the_title();
$coin_image_url = get_post_meta(get_the_ID(), '_crypto_image', true);
if (empty($coin_image_url) && $coin_data && isset($coin_data['image']['small'])) {
    $coin_image_url = $coin_data['image']['small'];
}
if (empty($coin_image_url) && has_post_thumbnail()) {
    $coin_image_url = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
}

$sparkline_prices = array();
if ($coin_data && isset($coin_data['market_data']) && isset($coin_data['market_data']['sparkline_7d']['price'])) {
    $sparkline_prices = $coin_data['market_data']['sparkline_7d']['price'];
} else {
    $meta_spark = get_post_meta(get_the_ID(), '_crypto_sparkline', true);
    if (!empty($meta_spark)) {
        $decoded = json_decode($meta_spark, true);
        if (is_array($decoded)) {
            $sparkline_prices = $decoded;
        }
    }
}

$global_market = $api->get_global_market_data();
$market_dominance = 0;
if ($global_market && isset($global_market['total_market_cap']['usd']) && $global_market['total_market_cap']['usd'] > 0 && $market_cap > 0) {
    $market_dominance = ($market_cap / $global_market['total_market_cap']['usd']) * 100;
}

$tickers = array();
$status_updates = array();
$related_posts = array();
$wp_news_posts = array();
if (!empty($coingecko_id)) {
    $tickers = $api->get_coin_tickers($coingecko_id, 1);
    $status_updates = $api->get_coin_status_updates($coingecko_id, 1, 10);
}
// اخبار وردپرس بر اساس دسته/برچسب (نام یا نماد ارز)
$coin_slug = sanitize_title($coin_name);
$sym_slug = strtolower($symbol);
$meta_news_cat = get_post_meta(get_the_ID(), '_crypto_news_category', true);
$meta_news_tag = get_post_meta(get_the_ID(), '_crypto_news_tag', true);

// لیست تمام slug های ممکن برای جستجو
$search_terms = array();
if (!empty($meta_news_tag)) {
    $search_terms[] = $meta_news_tag;
}
if (!empty($meta_news_cat)) {
    $search_terms[] = $meta_news_cat;
}
$search_terms[] = $sym_slug;
$search_terms[] = $coin_slug;
$search_terms[] = strtolower($symbol);
$search_terms[] = sanitize_title(str_replace(' ', '-', $coin_name));

// حذف موارد تکراری
$search_terms = array_unique(array_filter($search_terms));

// ساخت tax_query
$news_tax_query = array('relation' => 'OR');
if (!empty($search_terms)) {
    $news_tax_query[] = array(
        'taxonomy' => 'post_tag',
        'field' => 'slug',
        'terms' => $search_terms,
        'operator' => 'IN'
    );
    $news_tax_query[] = array(
        'taxonomy' => 'category',
        'field' => 'slug',
        'terms' => $search_terms,
        'operator' => 'IN'
    );
}

// Query اصلی با tax_query
$news_query_args = array(
    'post_type' => 'post',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
);

if (count($news_tax_query) > 1) {
    $news_query_args['tax_query'] = $news_tax_query;
}

$news_query = new WP_Query($news_query_args);
if ($news_query && $news_query->have_posts()) {
    $wp_news_posts = $news_query->posts;
    wp_reset_postdata();
}

// Fallback 1: جستجو بر اساس عنوان در محتوا
if (empty($wp_news_posts)) {
    $search_query = new WP_Query(array(
        'post_type' => 'post',
        's' => $coin_name,
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'orderby' => 'relevance'
    ));
    if ($search_query && $search_query->have_posts()) {
        $wp_news_posts = $search_query->posts;
        wp_reset_postdata();
    }
}

// Fallback 2: جستجو با symbol
if (empty($wp_news_posts) && !empty($symbol)) {
    $symbol_query = new WP_Query(array(
        'post_type' => 'post',
        's' => $symbol,
        'posts_per_page' => 10,
        'post_status' => 'publish',
    ));
    if ($symbol_query && $symbol_query->have_posts()) {
        $wp_news_posts = $symbol_query->posts;
        wp_reset_postdata();
    }
}

// Fallback 3: آخرین اخبار (اگر هنوز چیزی پیدا نشد)
if (empty($wp_news_posts)) {
    $related_posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => 6,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}
?>

<main class="single-crypto" data-coingecko-id="<?php echo esc_attr($coingecko_id); ?>">
    <div class="container">
        
        <!-- Header -->
        <div class="crypto-header">
            <div class="crypto-title-actions">
                <div class="crypto-title-section">
                    <img src="<?php echo esc_url($coin_image_url); ?>" alt="<?php echo esc_attr($coin_name); ?>" class="crypto-logo" />
                    <div>
                        <h1><?php echo esc_html($coin_name); ?></h1>
                        <div class="badges">
                            <span class="badge symbol-badge"><?php echo strtoupper($symbol); ?></span>
                            <span class="badge rank-badge">Rank #<?php echo intval($rank); ?></span>
                        </div>
                    </div>
                </div>
                <div class="header-icon-actions">
                    <button class="icon-btn" id="fav-toggle" aria-label="افزودن به علاقه‌مندی">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9 12 2"></polygon></svg>
                    </button>
                    <button class="icon-btn" id="share-btn" aria-label="اشتراک‌گذاری">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                    </button>
                </div>
            </div>
            <div class="price-inline">
                <div class="price-number">
                    <span id="price-usd-inline">
                        <?php echo $price_usd > 0 ? crypto_sekhyab_format_price_usd($price_usd) : '—'; ?>
                    </span>
                    <span class="change-24h <?php echo $change_24h >= 0 ? 'green' : 'red'; ?>" id="change-24h-inline">
                        <?php echo $change_24h >= 0 ? '▲' : '▼'; ?> <?php echo number_format(abs($change_24h), 2); ?>%
                    </span>
                </div>
                <div class="price-sub">
                    <span id="price-irr-inline"><?php echo $price_irr > 0 ? number_format($price_irr, 0) . ' تومان' : '—'; ?></span>
                    <?php if (!empty($sparkline_prices)) : ?>
                    <span class="mini-sparkline" id="mini-sparkline"></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tabs-nav" role="tablist">
                <button class="tab-btn active" data-tab="overview" role="tab">نمودار</button>
                <button class="tab-btn" data-tab="details" role="tab">جزئیات</button>
                <button class="tab-btn" data-tab="markets" role="tab">بازارها</button>
                <button class="tab-btn" data-tab="history" role="tab">تاریخچه</button>
                <button class="tab-btn" data-tab="news" role="tab">اخبار</button>
                <button class="tab-btn" data-tab="about" role="tab">درباره</button>
            </div>
        </div>

        <!-- لایه‌بندی اصلی صفحه: ستون محتوا + سایدبار -->
        <div class="page-grid">
        <div class="main-col">

        <!-- قیمت -->
        <div class="price-section" id="tab-overview" data-tab-content="overview">
            <!-- کارت قیمت و تغییرات -->
            <div class="price-card combined main">
                <div class="price-info-group">
                    <div class="label">💰 قیمت لحظه‌ای</div>
                    <div class="price-big" id="price-irr">
                        <?php 
                        if ($price_irr > 0) {
                            echo number_format($price_irr, 0) . ' تومان';
                        } else {
                            echo '<span class="loading">در حال بارگذاری...</span>';
                        }
                        ?>
                    </div>
                    <div class="price-small" id="price-usd">
                        <?php 
                        if ($price_usd > 0) {
                            if ($price_usd >= 1) {
                                echo '$' . number_format($price_usd, 2);
                            } elseif ($price_usd >= 0.01) {
                                echo '$' . number_format($price_usd, 4);
                            } else {
                                echo '$' . number_format($price_usd, 8);
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="divider-vertical"></div>
                
                <div class="price-info-group">
                    <div class="label">📊 تغییرات 24 ساعت</div>
                    <div class="value <?php echo $change_24h >= 0 ? 'green' : 'red'; ?>" id="change-24h">
                        <?php echo $change_24h >= 0 ? '▲' : '▼'; ?>
                        <?php echo number_format(abs($change_24h), 2); ?>%
                    </div>
                    <div class="sub-changes">
                        <span>7d: <strong class="<?php echo $change_7d >= 0 ? 'green' : 'red'; ?>"><?php echo number_format($change_7d, 1); ?>%</strong></span>
                        <span>30d: <strong class="<?php echo $change_30d >= 0 ? 'green' : 'red'; ?>"><?php echo number_format($change_30d, 1); ?>%</strong></span>
                    </div>
                </div>
            </div>
            
            <!-- کارت بازار و حجم -->
            <div class="price-card combined">
                <div class="price-info-group">
                    <div class="label">🏆 ارزش بازار</div>
                    <div class="value" id="market-cap">
                        <?php 
                        if ($market_cap >= 1000000000) {
                            echo '$' . number_format($market_cap / 1000000000, 2) . 'B';
                        } elseif ($market_cap >= 1000000) {
                            echo '$' . number_format($market_cap / 1000000, 0) . 'M';
                        } else {
                            echo '$' . number_format($market_cap, 0);
                        }
                        ?>
                    </div>
                    <div class="sub-info">Market Cap</div>
                </div>
                
                <div class="divider-vertical"></div>
                
                <div class="price-info-group">
                    <div class="label">💎 حجم معاملات 24h</div>
                    <div class="value" id="volume-24h">
                        <?php 
                        if ($volume_24h >= 1000000000) {
                            echo '$' . number_format($volume_24h / 1000000000, 2) . 'B';
                        } elseif ($volume_24h >= 1000000) {
                            echo '$' . number_format($volume_24h / 1000000, 0) . 'M';
                        } else {
                            echo '$' . number_format($volume_24h, 0);
                        }
                        ?>
                    </div>
                    <div class="sub-info">حجم مبادلات</div>
                </div>
            </div>
        </div>
        
        <!-- مبدل ارز -->
        <div class="converter-section" id="converter" data-tab-content="overview">
            <h2>🔄 مبدل</h2>
            <div class="converter-box">
                <div class="conv-input">
                    <label>مقدار رمزارز (<?php echo esc_html(strtoupper($symbol)); ?>)</label>
                    <div class="input-wrap"><input type="number" step="any" id="conv-crypto" value="1" /></div>
                    <div class="conv-symbol"><?php echo esc_html(strtoupper($symbol)); ?></div>
                </div>
                <button class="conv-swap" id="conv-swap" aria-label="تبدیل">↔</button>
                <div class="conv-input">
                    <label>مقدار تبدیل‌شده</label>
                    <div class="input-wrap"><input type="number" step="any" id="conv-fiat" /></div>
                    <select id="conv-currency">
                        <option value="irr" selected>تومان</option>
                        <option value="usd">USD</option>
                    </select>
                </div>
            </div>
            <div class="conv-hint">نرخ لحظه‌ای</div>
        </div>

        <!-- اطلاعات اضافی -->
        <div class="additional-stats" id="tab-details" data-tab-content="details" style="display:none;">
            <div class="stat-row">
                <div class="stat-box">
                    <div class="stat-label">🔝 بالاترین قیمت (ATH)</div>
                    <div class="stat-value">
                        <?php 
                        if ($ath > 0) {
                            echo '$' . number_format($ath, 2);
                            if ($ath_change != 0) {
                                echo ' <span class="stat-change ' . ($ath_change >= 0 ? 'green' : 'red') . '">(' . number_format($ath_change, 1) . '%)</span>';
                            }
                        } else {
                            echo 'نامشخص';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">🔻 پایین‌ترین قیمت (ATL)</div>
                    <div class="stat-value">
                        <?php 
                        if ($atl > 0) {
                            echo '$' . number_format($atl, 8);
                            if ($atl_change != 0) {
                                echo ' <span class="stat-change ' . ($atl_change >= 0 ? 'green' : 'red') . '">(' . number_format($atl_change, 1) . '%)</span>';
                            }
                        } else {
                            echo 'نامشخص';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">🔄 عرضه در گردش</div>
                    <div class="stat-value">
                        <?php 
                        if ($circulating_supply > 0) {
                            echo number_format($circulating_supply, 0) . ' ' . $symbol;
                            if ($max_supply > 0) {
                                $percent = ($circulating_supply / $max_supply) * 100;
                                echo '<div class="progress-bar"><div class="progress-fill" style="width: ' . $percent . '%"></div></div>';
                                echo '<small>' . number_format($percent, 1) . '% از ' . number_format($max_supply, 0) . '</small>';
                            }
                        } else {
                            echo 'نامشخص';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">📈 رتبه بازار</div>
                    <div class="stat-value">
                        <?php echo $rank ? '#' . $rank : 'نامشخص'; ?>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-label">📉 کمترین/بیشترین 24ساعته</div>
                    <div class="stat-value">
                        <?php 
                        if ($low_24h > 0 || $high_24h > 0) {
                            echo '$' . number_format($low_24h, 4) . ' — ' . '$' . number_format($high_24h, 4);
                        } else {
                            echo 'نامشخص';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- نمودارها -->
        <div class="charts-section" id="charts-section" data-tab-content="overview">
            <div class="chart-box large-chart">
                <div class="chart-header">
                    <h2>📊 نمودار قیمت</h2>
                    <div class="chart-controls-wrapper">
                        <div class="currency-toggle" role="group" aria-label="currency toggle">
                            <button type="button" class="toggle-currency active" data-currency="irr">تومان</button>
                            <button type="button" class="toggle-currency" data-currency="usd">دلار</button>
                        </div>
                        <div class="chart-range-buttons" role="group" aria-label="range toggle">
                            <button type="button" class="chart-range" data-days="1">24h</button>
                            <button type="button" class="chart-range active" data-days="7">7d</button>
                            <button type="button" class="chart-range" data-days="30">30d</button>
                            <button type="button" class="chart-range" data-days="90">90d</button>
                            <button type="button" class="chart-range" data-days="180">6m</button>
                            <button type="button" class="chart-range" data-days="365">1y</button>
                            <button type="button" class="chart-range" data-days="max">همه</button>
                        </div>
                    </div>
                </div>
                <div class="chart-area">
                    <canvas id="irr-chart"></canvas>
                    <div class="chart-loader" id="irr-loader">
                        <div class="loader-spinner"></div>
                        <span>در حال بارگذاری...</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-box large-chart">
                <div class="chart-header">
                    <h2>📈 نمودار تکنیکال TradingView</h2>
                    <div class="chart-range-buttons tv-controls" role="group" aria-label="tv timeframe">
                        <button type="button" class="tv-range active" data-interval="D">روزانه</button>
                        <button type="button" class="tv-range" data-interval="W">هفتگی</button>
                        <button type="button" class="tv-range" data-interval="M">ماهانه</button>
                    </div>
                </div>
                <div id="tv-chart" class="tv-chart-container"></div>
            </div>
        </div>

        <!-- بازارها -->
        <div class="markets-section" id="tab-markets" data-tab-content="markets" style="display:none;">
            <h2>📈 بازارها</h2>
            <div class="markets-table-wrapper">
                <table class="markets-table">
                    <thead>
                        <tr>
                            <th>صرافی</th>
                            <th>جفت</th>
                            <th>قیمت</th>
                            <th>حجم 24h</th>
                            <th>تغییر 24h</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($tickers)) {
                            $shown = 0;
                            foreach ($tickers as $t) {
                                if ($shown >= 15) break;
                                $exchange = isset($t['market']['name']) ? $t['market']['name'] : '';
                                $ex_logo = isset($t['market']['logo']) ? $t['market']['logo'] : '';
                                $base = isset($t['base']) ? $t['base'] : '';
                                $target = isset($t['target']) ? $t['target'] : '';
                                $last = isset($t['last']) ? floatval($t['last']) : 0;
                                $volume = isset($t['volume']) ? floatval($t['volume']) : 0;
                                $isUSD = (stripos($target, 'USD') !== false || stripos($target, 'USDT') !== false);
                                $priceText = $isUSD ? crypto_sekhyab_format_price_usd($last) : number_format($last, 6);
                                echo '<tr>';
                                echo '<td class="ex-name">' . (!empty($ex_logo) ? '<img src="' . esc_url($ex_logo) . '" alt="' . esc_attr($exchange) . '" class="ex-logo"/>' : '') . '<span>' . esc_html($exchange) . '</span></td>';
                                echo '<td class="pair">' . esc_html($base . '/' . $target) . '</td>';
                                echo '<td class="price">' . esc_html($priceText) . '</td>';
                                echo '<td class="volume">$' . number_format($volume, 0) . '</td>';
                                echo '<td class="change">-</td>';
                                echo '</tr>';
                                $shown++;
                            }
                        } else {
                            echo '<tr><td colspan="5">بازاری یافت نشد.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- اخبار -->
        <div class="news-section" id="tab-news" data-tab-content="news" style="display:none;">
            <div class="news-header">
                <h2>📰 اخبار و بروزرسانی‌ها</h2>
                <a href="#" class="see-all-news">مشاهده همه اخبار</a>
            </div>
            <div class="news-cards">
                <?php 
                $has_news = false;
                if (!empty($status_updates)) : 
                    $has_news = true;
                    foreach ($status_updates as $u) : 
                        $title = isset($u['project']['name']) ? $u['project']['name'] : $coin_name; 
                        $desc = isset($u['description']) ? $u['description'] : '';
                        $category = isset($u['category']) ? $u['category'] : 'خبر';
                        $time = isset($u['created_at']) ? date_i18n('Y/m/d H:i', strtotime($u['created_at'])) : '';
                        $thumb = isset($u['project']['image']['small']) ? $u['project']['image']['small'] : $coin_image_url;
                        ?>
                        <div class="news-card">
                            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" class="news-thumb" />
                            <div class="news-meta">
                                <span class="news-tag"><?php echo esc_html($category); ?></span>
                                <span class="news-time"><?php echo esc_html($time); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo esc_html(wp_trim_words($desc, 14)); ?></h3>
                        </div>
                    <?php endforeach; 
                endif; 
                
                // اگر status_updates خالی بود، از اخبار وردپرس استفاده کن
                if (!$has_news && !empty($wp_news_posts)) : 
                    $has_news = true;
                    foreach ($wp_news_posts as $p) : ?>
                        <div class="news-card">
                            <a href="<?php echo esc_url(get_permalink($p)); ?>">
                                <?php if (has_post_thumbnail($p)) : ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($p, 'medium')); ?>" class="news-thumb" alt="<?php echo esc_attr(get_the_title($p)); ?>" />
                                <?php else : ?>
                                    <div class="news-thumb news-thumb-placeholder">
                                        <span class="news-icon">📰</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="news-meta">
                                <span class="news-tag">خبر</span>
                                <span class="news-time"><?php echo esc_html(get_the_date('Y/m/d', $p)); ?></span>
                            </div>
                            <h3 class="news-title"><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html(get_the_title($p)); ?></a></h3>
                        </div>
                    <?php endforeach;
                endif;
                
                // fallback به مطالب مرتبط
                if (!$has_news && !empty($related_posts)) : 
                    $has_news = true;
                    foreach ($related_posts as $p) : ?>
                        <div class="news-card">
                            <a href="<?php echo esc_url(get_permalink($p)); ?>">
                                <?php if (has_post_thumbnail($p)) : ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($p, 'medium')); ?>" class="news-thumb" alt="<?php echo esc_attr(get_the_title($p)); ?>" />
                                <?php else : ?>
                                    <div class="news-thumb news-thumb-placeholder">
                                        <span class="news-icon">📄</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="news-meta">
                                <span class="news-tag">مطلب مرتبط</span>
                                <span class="news-time"><?php echo esc_html(get_the_date('Y/m/d', $p)); ?></span>
                            </div>
                            <h3 class="news-title"><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html(get_the_title($p)); ?></a></h3>
                        </div>
                    <?php endforeach;
                endif;
                
                if (!$has_news) : ?>
                    <div class="news-empty">
                        <div class="empty-icon">📭</div>
                        <p>در حال حاضر خبری برای نمایش وجود ندارد</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- تاریخچه -->
        <div class="history-section" id="tab-history" data-tab-content="history" style="display:none;">
            <div class="history-header">
                <h2>🗂️ تاریخچه قیمت</h2>
                <div class="chart-controls" role="group" aria-label="history range">
                    <button type="button" class="btn btn-light history-range" data-days="7">7d</button>
                    <button type="button" class="btn btn-light history-range active" data-days="30">30d</button>
                    <button type="button" class="btn btn-light history-range" data-days="365">1y</button>
                    <button type="button" class="btn btn-light history-range" data-days="max">All</button>
                </div>
            </div>
            <div class="history-table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>قیمت دلار</th>
                            <th>قیمت تومان</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                        <tr><td colspan="3">در حال بارگذاری...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ویدیوها (جای‌نگهدار) -->
        <div class="videos-section" id="tab-videos" data-tab-content="videos" style="display:none;">
            <h2>🎬 ویدیوها</h2>
            <p>به زودی...</p>
        </div>

        <!-- توضیحات -->
        <div class="content-section" id="tab-about" data-tab-content="about" style="display:none;">
            <?php the_content(); ?>
            <?php if (!get_the_content()) : ?>
                <p style="color: #64748b;">توضیحاتی برای این ارز ثبت نشده است.</p>
            <?php endif; ?>
        </div>

        </div><!-- /.main-col -->

        <!-- سایدبار -->
        <aside class="sidebar-col">
            <div class="side-card">
                <div class="side-head">
                    <img src="<?php echo esc_url($coin_image_url); ?>" class="side-logo" alt="" />
                    <div>
                        <div class="side-title"><?php echo esc_html($coin_name); ?> <span class="side-symbol"><?php echo esc_html(strtoupper($symbol)); ?></span></div>
                        <div class="side-rank">رتبه بازار: <strong>#<?php echo intval($rank); ?></strong></div>
                    </div>
                </div>
                <div class="side-price">
                    <div class="side-usd" id="side-price-usd"><?php echo $price_usd > 0 ? crypto_sekhyab_format_price_usd($price_usd) : '—'; ?></div>
                    <div class="side-irr" id="side-price-irr"><?php echo $price_irr > 0 ? number_format($price_irr, 0) . ' تومان' : '—'; ?></div>
                    <div class="side-change <?php echo $change_24h >= 0 ? 'green' : 'red'; ?>" id="side-change"><?php echo $change_24h >= 0 ? '▲' : '▼'; ?> <?php echo number_format(abs($change_24h), 2); ?>%</div>
                </div>
                <div class="side-sparkline" id="side-sparkline"></div>
            </div>

            <?php if (get_option('crypto_sekhyab_show_exchange_links', '1') === '1') : ?>
            <div class="side-card">
                <h3 class="side-card-title">لینک سریع به صرافی‌ها</h3>
                <ul class="exchange-links">
                    <?php
                    $custom_links = trim((string) get_option('crypto_sekhyab_exchange_links', ''));
                    if (!empty($custom_links)) {
                        $lines = preg_split('/\r?\n/', $custom_links);
                        foreach ($lines as $line) {
                            $parts = array_map('trim', explode('|', $line));
                            if (count($parts) >= 2) {
                                echo '<li><a href="' . esc_url($parts[1]) . '" target="_blank" rel="nofollow noopener">' . esc_html($parts[0]) . '</a></li>';
                            }
                        }
                    } else {
                        // fallback: از تیکرها
                        $links_shown = 0;
                        if (!empty($tickers)) {
                            foreach ($tickers as $t) {
                                if ($links_shown >= 6) break;
                                $name = isset($t['market']['name']) ? $t['market']['name'] : '';
                                $url = isset($t['trade_url']) ? $t['trade_url'] : '';
                                if (!$name || !$url) continue;
                                echo '<li><a href="' . esc_url($url) . '" target="_blank" rel="nofollow noopener">' . esc_html($name) . '</a></li>';
                                $links_shown++;
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (get_option('crypto_sekhyab_show_ad_banner', '1') === '1' && get_option('crypto_sekhyab_ad_show_single_crypto', '1') === '1') : ?>
            <div class="side-card ad-card">
                <a href="<?php echo esc_url(get_option('crypto_sekhyab_ad_banner_url', '#')); ?>" class="ad-banner" target="_blank" rel="nofollow noopener">
                    <?php echo esc_html(get_option('crypto_sekhyab_ad_banner_text', 'دانلود اپلیکیشن')); ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="side-card">
                <h3 class="side-card-title">محاسبه سریع به تومان</h3>
                <div class="mini-converter">
                    <div class="mini-row">
                        <input type="number" step="any" id="mini-crypto" value="1" />
                        <span><?php echo esc_html(strtoupper($symbol)); ?></span>
                    </div>
                    <div class="mini-row">
                        <input type="text" id="mini-irr" readonly />
                        <span>تومان</span>
                    </div>
                </div>
            </div>
        </aside>
        </div><!-- /.page-grid -->

        <!-- اخبار مرتبط قبل از کامنت‌ها -->
        <?php 
        // دیباگ: بررسی وجود اخبار
        $has_any_news = !empty($status_updates) || !empty($wp_news_posts) || !empty($related_posts);
        
        // اگر هیچ خبری نیست، آخرین پست‌ها رو بگیر
        if (!$has_any_news) {
            $latest_posts = get_posts(array(
                'post_type' => 'post',
                'posts_per_page' => 6,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            if (!empty($latest_posts)) {
                $related_posts = $latest_posts;
                $has_any_news = true;
            }
        }
        ?>
        
        <?php if ($has_any_news) : ?>
        <div class="related-news-section">
            <div class="news-header">
                <h2>📰 اخبار و مطالب مرتبط با <?php echo esc_html($coin_name); ?></h2>
                <div class="news-debug" style="font-size:11px;color:#64748b;">
                    <?php 
                    echo 'CoinGecko: ' . count($status_updates) . ' | ';
                    echo 'تگ/دسته: ' . count($wp_news_posts) . ' | ';
                    echo 'مرتبط: ' . count($related_posts);
                    ?>
                </div>
            </div>
            <div class="news-cards">
                <?php 
                $shown_news = 0;
                $max_news = 6;
                
                // اخبار CoinGecko
                if (!empty($status_updates) && $shown_news < $max_news) : 
                    foreach ($status_updates as $u) : 
                        if ($shown_news >= $max_news) break;
                        $title = isset($u['project']['name']) ? $u['project']['name'] : $coin_name; 
                        $desc = isset($u['description']) ? $u['description'] : '';
                        $category = isset($u['category']) ? $u['category'] : 'خبر';
                        $time = isset($u['created_at']) ? date_i18n('Y/m/d H:i', strtotime($u['created_at'])) : '';
                        $thumb = isset($u['project']['image']['small']) ? $u['project']['image']['small'] : $coin_image_url;
                        ?>
                        <div class="news-card">
                            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($title); ?>" class="news-thumb" />
                            <div class="news-meta">
                                <span class="news-tag"><?php echo esc_html($category); ?></span>
                                <span class="news-time"><?php echo esc_html($time); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo esc_html(wp_trim_words($desc, 14)); ?></h3>
                        </div>
                        <?php 
                        $shown_news++;
                    endforeach; 
                endif; 
                
                // اخبار وردپرس
                if (!empty($wp_news_posts) && $shown_news < $max_news) : 
                    foreach ($wp_news_posts as $p) : 
                        if ($shown_news >= $max_news) break;
                        ?>
                        <div class="news-card">
                            <a href="<?php echo esc_url(get_permalink($p)); ?>">
                                <?php if (has_post_thumbnail($p)) : ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($p, 'medium')); ?>" class="news-thumb" alt="<?php echo esc_attr(get_the_title($p)); ?>" />
                                <?php else : ?>
                                    <div class="news-thumb news-thumb-placeholder">
                                        <span class="news-icon">📰</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="news-meta">
                                <span class="news-tag">خبر</span>
                                <span class="news-time"><?php echo esc_html(get_the_date('Y/m/d', $p)); ?></span>
                            </div>
                            <h3 class="news-title"><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html(get_the_title($p)); ?></a></h3>
                        </div>
                        <?php 
                        $shown_news++;
                    endforeach;
                endif;
                
                // مطالب مرتبط
                if (!empty($related_posts) && $shown_news < $max_news) : 
                    foreach ($related_posts as $p) : 
                        if ($shown_news >= $max_news) break;
                        ?>
                        <div class="news-card">
                            <a href="<?php echo esc_url(get_permalink($p)); ?>">
                                <?php if (has_post_thumbnail($p)) : ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($p, 'medium')); ?>" class="news-thumb" alt="<?php echo esc_attr(get_the_title($p)); ?>" />
                                <?php else : ?>
                                    <div class="news-thumb news-thumb-placeholder">
                                        <span class="news-icon">📄</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="news-meta">
                                <span class="news-tag">خبر</span>
                                <span class="news-time"><?php echo esc_html(get_the_date('Y/m/d', $p)); ?></span>
                            </div>
                            <h3 class="news-title"><a href="<?php echo esc_url(get_permalink($p)); ?>"><?php echo esc_html(get_the_title($p)); ?></a></h3>
                        </div>
                        <?php 
                        $shown_news++;
                    endforeach;
                endif;
                
                // اگر هیچ خبری نمایش داده نشد، پیام نمایش بده
                if ($shown_news == 0) :
                ?>
                    <div class="news-empty">
                        <div class="empty-icon">📭</div>
                        <p>در حال حاضر خبری برای <?php echo esc_html($coin_name); ?> وجود ندارد</p>
                        <p style="font-size:13px;margin-top:8px;">
                            برای نمایش اخبار، به پست‌های خود تگ یا دسته‌بندی 
                            <strong><?php echo esc_html($symbol); ?></strong> یا 
                            <strong><?php echo esc_html($coin_name); ?></strong> 
                            را اضافه کنید.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // نمایش کامنت‌ها اگر فعال باشد
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>

    </div>
</main>

<script src="https://s3.tradingview.com/tv.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($) {
    const coingeckoId = '<?php echo esc_js($coingecko_id); ?>';
    let usdtRate = <?php echo floatval($usdt_rate); ?>;
    window.coingeckoId = coingeckoId;
    window.usdtRate = usdtRate;
    const tvSymbol = '<?php echo esc_js($tv_symbol); ?>';
    const priceUSDInit = <?php echo json_encode($price_usd); ?>;
    const usdtInit = <?php echo json_encode($usdt_rate); ?>;
    
    // تب‌ها - مدیریت تغییر تب
    $('.tab-btn').on('click', function() {
        const targetTab = $(this).data('tab');
        
        // به‌روزرسانی دکمه‌های تب
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        
        // مخفی کردن همه محتواها
        $('[data-tab-content]').hide();
        
        // نمایش محتوای تب انتخاب‌شده
        $('[data-tab-content="' + targetTab + '"]').fadeIn(300);
    });
    
    // TradingView با fallback بارگذاری تا آماده‌شدن script
    function initTV() {
        try {
            if (window.TradingView && typeof TradingView.widget === 'function') {
                new TradingView.widget({
                    width: '100%',
                    height: 500,
                    symbol: tvSymbol,
                    interval: 'D',
                    timezone: 'Asia/Tehran',
                    theme: 'light',
                    style: '1',
                    locale: 'fa',
                    toolbar_bg: '#ffffff',
                    enable_publishing: false,
                    hide_side_toolbar: false,
                    allow_symbol_change: true,
                    studies: [
                        "MASimple@tv-basicstudies",
                        "RSI@tv-basicstudies"
                    ],
                    container_id: 'tv-chart'
                });
                return true;
            }
        } catch (e) {
            console.error('TradingView init error:', e);
        }
        return false;
    }
    if (!initTV()) {
        const tvTimer = setInterval(function() {
            if (initTV()) { clearInterval(tvTimer); }
        }, 500);
        setTimeout(function() { try { clearInterval(tvTimer); } catch(e) {} }, 20000);
    }
    // تغییر تایم‌فریم نمودار تکنیکال
    $(document).on('click', '.tv-range', function() {
        $('.tv-range').removeClass('active');
        $(this).addClass('active');
        const interval = $(this).data('interval') || 'D';
        try {
            if (window.TradingView) {
                const container = document.getElementById('tv-chart');
                container.innerHTML = '';
                new TradingView.widget({
                    width: '100%',
                    height: 500,
                    symbol: tvSymbol,
                    interval: String(interval),
                    timezone: 'Asia/Tehran',
                    theme: 'light',
                    style: '1',
                    locale: 'fa',
                    toolbar_bg: '#ffffff',
                    enable_publishing: false,
                    hide_side_toolbar: false,
                    allow_symbol_change: true,
                    container_id: 'tv-chart'
                });
            }
        } catch(e) {
            console.error('TradingView error:', e);
        }
    });
    
    // نمودار تومانی/دلاری با سوییچ
    const ctx = document.getElementById('irr-chart').getContext('2d');
    let seriesUSD = [];
    let seriesIRR = [];
    let timeLabels = [];
    let timeStamps = [];
    let currentDays = 7;
    let currentCurrency = 'irr';
    
    const irrChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [{
                label: 'قیمت (تومان)',
                data: seriesIRR,
                borderColor: '#16c784',
                backgroundColor: 'rgba(22, 199, 132, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (currentCurrency === 'irr') {
                                return 'قیمت: ' + context.parsed.y.toLocaleString('fa-IR') + ' تومان';
                            }
                            const val = context.parsed.y;
                            let formatted;
                            if (val >= 1) formatted = '$' + val.toFixed(2);
                            else if (val >= 0.01) formatted = '$' + val.toFixed(4);
                            else formatted = '$' + val.toFixed(8);
                            return 'Price: ' + formatted;
                        },
                        title: function(items) {
                            if (!items || !items.length) return '';
                            const idx = items[0].dataIndex;
                            const ts = timeStamps[idx] ? timeStamps[idx] : null;
                            if (!ts) return '';
                            const d = new Date(ts);
                            const greg = d.toLocaleString('en-GB', { year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit' });
                            const jal = d.toLocaleString('fa-IR', { year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit' });
                            return greg + ' | ' + jal;
                        }
                    }
                }
            },
            scales: {
                x: { display: false },
                y: {
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('fa-IR');
                        }
                    }
                }
            }
        }
    });
    
    function updateChartDataSets() {
        const dataToUse = (currentCurrency === 'irr') ? seriesIRR : seriesUSD;
        irrChart.data.datasets[0].label = (currentCurrency === 'irr') ? 'قیمت (تومان)' : 'Price (USD)';
        irrChart.data.datasets[0].data = dataToUse;
        irrChart.update();
    }

    // دریافت داده‌های قیمت برای نمودار
    function setLoader(visible) {
        const el = document.getElementById('irr-loader');
        if (!el) return;
        el.style.display = visible ? 'flex' : 'none';
    }

    function updateIRRChart(days = currentDays) {
        currentDays = days;
        setLoader(true);
        $.ajax({
            url: (window.cryptoSekhyabData ? cryptoSekhyabData.ajaxUrl : '') || 'https://api.coingecko.com/api/v3/coins/' + coingeckoId + '/market_chart',
            method: (window.cryptoSekhyabData ? 'POST' : 'GET'),
            data: window.cryptoSekhyabData ? {
                action: 'get_market_chart',
                nonce: cryptoSekhyabData.nonce,
                coin_id: coingeckoId,
                days: days
            } : {
                vs_currency: 'usd',
                days: days
            },
            timeout: 12000,
            success: function(resp) {
                const data = (resp && resp.data && resp.success) ? resp.data : resp;
                if (data.prices) {
                    seriesUSD = [];
                    seriesIRR = [];
                    timeLabels = [];
                    timeStamps = [];
                    // نمونه‌گیری پویا برای جلوگیری از نقاط زیاد
                    const len = data.prices.length;
                    const targetPoints = days === 1 ? 48 : (days === 7 ? 84 : 120);
                    const step = Math.max(1, Math.floor(len / targetPoints));
                    for (let i = 0; i < len; i += step) {
                        const [timestamp, priceUSD] = data.prices[i];
                        const priceIRR = priceUSD * usdtRate;
                        seriesUSD.push(priceUSD);
                        seriesIRR.push(priceIRR);
                        const date = new Date(timestamp);
                        let label;
                        if (days === 1) {
                            const h = String(date.getHours()).padStart(2, '0');
                            const m = String(date.getMinutes()).padStart(2, '0');
                            label = h + ':' + m;
                        } else {
                            label = date.getDate() + '/' + (date.getMonth() + 1);
                        }
                        timeLabels.push(label);
                        timeStamps.push(timestamp);
                    }
                    
                    irrChart.data.labels = timeLabels;
                    updateChartDataSets();
                }
                setLoader(false);
            },
            error: function() {
                setLoader(false);
            }
        });
    }
    
    // کنترل‌های بازه زمانی
    $('.chart-range').on('click', function() {
        $('.chart-range').removeClass('active');
        $(this).addClass('active');
        const raw = $(this).data('days');
        const days = (raw === 'max') ? 'max' : parseInt(raw, 10) || 7;
        updateIRRChart(days);
    });

    $('.toggle-currency').on('click', function() {
        $('.toggle-currency').removeClass('active');
        $(this).addClass('active');
        const cur = $(this).data('currency');
        currentCurrency = (cur === 'usd') ? 'usd' : 'irr';
        updateChartDataSets();
    });

    // دریافت اولیه (7 روز)
    updateIRRChart(7);
    // مینی‌اسپارکلاین کنار قیمت
    try {
        const sparkline = <?php echo wp_json_encode(array_slice($sparkline_prices, -50)); ?>;
        const renderSpark = (elId) => {
            const el = document.getElementById(elId);
            if (!el || !Array.isArray(sparkline) || !sparkline.length) return;
            const minVal = Math.min.apply(null, sparkline);
            const maxVal = Math.max.apply(null, sparkline);
            const normalized = sparkline.map(v => (v - minVal) / (maxVal - minVal + 1e-9));
            const points = normalized.map((n, i) => `${i},${(1-n).toFixed(3)}`).join(' ');
            el.innerHTML = `
                <svg viewBox="0 0 ${normalized.length} 1" preserveAspectRatio="none" class="spark-svg">
                    <polyline points="${points}" fill="none" stroke="${(sparkline[sparkline.length-1] - sparkline[0])>=0 ? '#16c784' : '#ea3943'}" stroke-width="0.08" />
                </svg>`;
        };
        renderSpark('mini-sparkline');
        renderSpark('side-sparkline');
    } catch(e) {}
    
    // تابع بروزرسانی قیمت
    function updatePrice() {
        if (!coingeckoId) {
            console.warn('No CoinGecko ID available');
            return;
        }
        
        $.ajax({
            url: 'https://api.coingecko.com/api/v3/simple/price',
            data: {
                ids: coingeckoId,
                vs_currencies: 'usd',
                include_24hr_change: true,
                include_24hr_vol: true,
                include_market_cap: true
            },
            timeout: 10000,
            success: function(data) {
                if (data[coingeckoId]) {
                    const coin = data[coingeckoId];
                    const priceUSD = coin.usd || 0;
                    const priceIRR = priceUSD * usdtRate;
                    
                    // بروزرسانی قیمت
                    if (priceUSD > 0) {
                        let formattedUSD;
                        if (priceUSD >= 1) {
                            formattedUSD = '$' + priceUSD.toFixed(2);
                        } else if (priceUSD >= 0.01) {
                            formattedUSD = '$' + priceUSD.toFixed(4);
                        } else {
                            formattedUSD = '$' + priceUSD.toFixed(8);
                        }
                        
                        $('#price-irr').html(priceIRR.toLocaleString('fa-IR', {maximumFractionDigits: 0}) + ' تومان');
                        $('#price-usd').text(formattedUSD);
                        $('#side-price-irr').text(priceIRR.toLocaleString('fa-IR', {maximumFractionDigits: 0}) + ' تومان');
                        $('#side-price-usd').text(formattedUSD);
                    }

                    // همگام‌سازی قیمت‌های بالا با هدر
                    try {
                        $('#price-usd-inline').text(formattedUSD);
                        $('#price-irr-inline').text(priceIRR.toLocaleString('fa-IR', {maximumFractionDigits: 0}) + ' تومان');
                        if (coin.usd_24h_change !== undefined) {
                            const inlineChange = coin.usd_24h_change;
                            $('#change-24h-inline')
                                .removeClass('green red')
                                .addClass(inlineChange >= 0 ? 'green' : 'red')
                                .html((inlineChange >= 0 ? '▲' : '▼') + ' ' + Math.abs(inlineChange).toFixed(2) + '%');
                        }
                        $('#side-change')
                            .removeClass('green red')
                            .addClass(inlineChange >= 0 ? 'green' : 'red')
                            .html((inlineChange >= 0 ? '▲' : '▼') + ' ' + Math.abs(inlineChange).toFixed(2) + '%');
                    } catch(e) {}
                    
                    // بروزرسانی تغییرات
                    if (coin.usd_24h_change !== undefined) {
                        const change = coin.usd_24h_change;
                        $('#change-24h').removeClass('green red').addClass(change >= 0 ? 'green' : 'red');
                        $('#change-24h').html((change >= 0 ? '▲' : '▼') + ' ' + Math.abs(change).toFixed(2) + '%');
                    }
                    
                    // بروزرسانی حجم
                    if (coin.usd_24h_vol) {
                        const vol = coin.usd_24h_vol;
                        let volText;
                        if (vol >= 1000000000) {
                            volText = '$' + (vol/1000000000).toFixed(2) + 'B';
                        } else if (vol >= 1000000) {
                            volText = '$' + (vol/1000000).toFixed(0) + 'M';
                        } else {
                            volText = '$' + vol.toFixed(0);
                        }
                        $('#volume-24h').text(volText);
                    }
                    
                    // بروزرسانی مارکت کپ
                    if (coin.usd_market_cap) {
                        const cap = coin.usd_market_cap;
                        let capText;
                        if (cap >= 1000000000) {
                            capText = '$' + (cap/1000000000).toFixed(2) + 'B';
                        } else if (cap >= 1000000) {
                            capText = '$' + (cap/1000000).toFixed(0) + 'M';
                        } else {
                            capText = '$' + cap.toFixed(0);
                        }
                        $('#market-cap').text(capText);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating price:', error);
                // نمایش پیام خطا به کاربر (اختیاری)
            }
        });
    }
    
    // بروزرسانی اولیه سریع و سپس دوره‌ای
    setTimeout(function() { updatePrice(); }, 1500);
    setInterval(updatePrice, 10000);
    // مبدل
    function computeConverter(fromCrypto = true) {
        const cur = ($('#conv-currency').val() || 'irr');
        const priceUSD = priceUSDInit || 0;
        const usdToIrr = usdtInit || usdtRate || 0;
        if (fromCrypto) {
            const c = parseFloat($('#conv-crypto').val() || '0');
            const usd = c * priceUSD;
            if (cur === 'usd') $('#conv-fiat').val(usd.toFixed(6));
            else $('#conv-fiat').val((usd * usdToIrr).toFixed(0));
        } else {
            const v = parseFloat($('#conv-fiat').val() || '0');
            const base = (cur === 'usd') ? v : (v / (usdToIrr || 1));
            const crypto = (priceUSD > 0) ? (base / priceUSD) : 0;
            $('#conv-crypto').val(crypto.toFixed(8));
        }
    }
    computeConverter(true);
    $(document).on('input change', '#conv-crypto', function(){ computeConverter(true); });
    $(document).on('input change', '#conv-fiat, #conv-currency', function(){ computeConverter(false); });
    $('#conv-swap').on('click', function(e){ e.preventDefault(); const c1=$('#conv-crypto').val(); const c2=$('#conv-fiat').val(); $('#conv-crypto').val(c2); $('#conv-fiat').val(c1); computeConverter(false); });

    // مینی‌مبدل سایدبار
    function computeMini() {
        const c = parseFloat($('#mini-crypto').val() || '0');
        const irr = c * (priceUSDInit || 0) * (usdtInit || usdtRate || 0);
        $('#mini-irr').val(irr.toLocaleString('fa-IR'));
    }
    computeMini();
    $(document).on('input change', '#mini-crypto', computeMini);
    
    // تاریخچه قیمت
    function loadHistory(days = 30) {
        const tbody = $('#history-body');
        tbody.html('<tr><td colspan="3" style="text-align:center;">در حال بارگذاری...</td></tr>');
        
        $.ajax({
            url: 'https://api.coingecko.com/api/v3/coins/' + coingeckoId + '/market_chart',
            data: {
                vs_currency: 'usd',
                days: days
            },
            success: function(data) {
                if (data && data.prices && data.prices.length) {
                    let html = '';
                    const step = Math.max(1, Math.floor(data.prices.length / 30));
                    for (let i = data.prices.length - 1; i >= 0; i -= step) {
                        const [ts, priceUSD] = data.prices[i];
                        const priceIRR = priceUSD * usdtRate;
                        const date = new Date(ts).toLocaleDateString('fa-IR');
                        html += '<tr>';
                        html += '<td>' + date + '</td>';
                        html += '<td>$' + priceUSD.toFixed(priceUSD >= 1 ? 2 : 6) + '</td>';
                        html += '<td>' + priceIRR.toLocaleString('fa-IR', {maximumFractionDigits: 0}) + ' تومان</td>';
                        html += '</tr>';
                        if (html.split('<tr>').length > 31) break;
                    }
                    tbody.html(html);
                } else {
                    tbody.html('<tr><td colspan="3" style="text-align:center;">داده‌ای یافت نشد</td></tr>');
                }
            },
            error: function() {
                tbody.html('<tr><td colspan="3" style="text-align:center;">خطا در بارگذاری داده‌ها</td></tr>');
            }
        });
    }
    
    $('.history-range').on('click', function() {
        $('.history-range').removeClass('active');
        $(this).addClass('active');
        const days = $(this).data('days');
        loadHistory(days === 'max' ? 365 : days);
    });
    
    // بارگذاری اولیه تاریخچه فقط وقتی تب فعال شد
    let historyLoaded = false;
    $('[data-tab="history"]').on('click', function() {
        if (!historyLoaded) {
            loadHistory(30);
            historyLoaded = true;
        }
    });
    
    // دکمه‌های اشتراک‌گذاری و علاقه‌مندی
    $('#share-btn').on('click', function() {
        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: window.location.href
            }).catch(err => console.log('Error sharing:', err));
        } else {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('لینک کپی شد!');
            });
        }
    });
    
    $('#fav-toggle').on('click', function() {
        $(this).toggleClass('favorited');
        const isFav = $(this).hasClass('favorited');
        const favs = JSON.parse(localStorage.getItem('crypto_favorites') || '[]');
        if (isFav) {
            if (!favs.includes(coingeckoId)) {
                favs.push(coingeckoId);
            }
            $(this).find('svg').attr('fill', 'currentColor');
        } else {
            const idx = favs.indexOf(coingeckoId);
            if (idx > -1) favs.splice(idx, 1);
            $(this).find('svg').attr('fill', 'none');
        }
        localStorage.setItem('crypto_favorites', JSON.stringify(favs));
    });
    
    // بررسی وضعیت علاقه‌مندی
    const favs = JSON.parse(localStorage.getItem('crypto_favorites') || '[]');
    if (favs.includes(coingeckoId)) {
        $('#fav-toggle').addClass('favorited').find('svg').attr('fill', 'currentColor');
    }
});
</script>

<style>
.single-crypto {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    padding: 40px 0;
    min-height: 100vh;
}

.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.page-grid { 
    display: grid; 
    grid-template-columns: minmax(0, 1fr) 360px; 
    gap: 28px; 
    align-items: start; 
    clear: both;
    margin-top: 28px;
}
.main-col { 
    min-width: 0; 
    position: relative;
}
.sidebar-col { 
    position: sticky;
    top: 24px;
    display: flex; 
    flex-direction: column; 
    gap: 18px; 
    min-width: 0; 
    align-self: start;
}
.price-section, .additional-stats, .charts-section, .markets-section, .news-section { 
    position: relative; 
    z-index: 1;
    animation: fadeInUp 0.5s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.side-card { 
    background: white; 
    border: 1px solid #e2e8f0; 
    border-radius: 18px; 
    padding: 20px; 
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}
.side-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
.side-head { 
    display: flex; 
    gap: 14px; 
    align-items: center; 
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.side-logo { 
    width: 48px; 
    height: 48px; 
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.side-title { 
    font-weight: 900; 
    color: #0f172a;
    font-size: 16px;
}
.side-symbol { 
    color: #64748b; 
    font-weight: 800; 
    margin-right: 6px;
    font-size: 14px;
}
.side-rank {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}
.side-price { 
    display: flex; 
    flex-direction: column; 
    gap: 6px; 
    font-weight: 800;
    padding: 12px 0;
}
.side-usd { 
    color: #0f172a;
    font-size: 18px;
}
.side-irr { 
    color: #64748b;
    font-size: 14px;
}
.side-change { 
    font-size: 14px;
    font-weight: 700;
}
.side-change.green { color: #16c784; }
.side-change.red { color: #ea3943; }
.side-sparkline {
    height: 60px;
    margin-top: 8px;
}
.side-card-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}
.exchange-links { 
    list-style: none; 
    padding: 0; 
    margin: 0; 
    display: grid; 
    gap: 10px; 
}
.exchange-links li {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 8px;
}
.exchange-links li:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.exchange-links a { 
    color: #475569; 
    text-decoration: none; 
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}
.exchange-links a:hover { 
    color: #6366f1;
    transform: translateX(-4px);
}
.exchange-links a::before {
    content: '→';
    color: #6366f1;
    font-weight: bold;
}
.ad-card .ad-banner { 
    display: block; 
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); 
    padding: 24px; 
    border-radius: 16px; 
    text-align: center; 
    font-weight: 900; 
    color: #ffffff;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
}
.ad-card .ad-banner:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
}
.mini-converter .mini-row { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    margin-bottom: 12px; 
}
.mini-converter .mini-row:last-child {
    margin-bottom: 0;
}
.mini-converter input { 
    width: 100%; 
    border: 1px solid #e2e8f0; 
    border-radius: 12px; 
    padding: 12px;
    font-weight: 700;
    color: #0f172a;
    transition: all 0.3s ease;
}
.mini-converter input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.mini-converter span {
    font-weight: 700;
    color: #64748b;
    font-size: 14px;
    min-width: 60px;
    text-align: right;
}

.converter-section { 
    background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%); 
    padding: 32px; 
    border-radius: 20px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
    margin-bottom: 28px;
    border: 1px solid #e2e8f0;
}
.converter-box { 
    display: grid; 
    grid-template-columns: 1fr auto 1fr; 
    gap: 16px; 
    align-items: end; 
}
.conv-input label { 
    display: block; 
    font-size: 13px; 
    color: #64748b; 
    margin-bottom: 8px; 
    font-weight: 700; 
}
.conv-input .input-wrap { 
    background: #ffffff; 
    border: 2px solid #e2e8f0; 
    border-radius: 14px; 
    padding: 12px;
    transition: all 0.3s ease;
}
.conv-input .input-wrap:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
.conv-input input, .conv-input select { 
    width: 100%; 
    border: none; 
    background: transparent; 
    font-weight: 800; 
    color: #0f172a; 
    outline: none;
    font-size: 16px;
}
.conv-symbol { 
    font-size: 13px; 
    color: #64748b; 
    margin-top: 6px; 
    font-weight: 700; 
}
.conv-swap { 
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); 
    border: none;
    border-radius: 14px; 
    padding: 14px 16px; 
    font-weight: 900; 
    cursor: pointer;
    color: white;
    transition: all 0.3s ease;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.conv-swap:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}
.conv-hint { 
    font-size: 13px; 
    color: #64748b; 
    margin-top: 12px;
    font-weight: 600;
}

.back-link {
    display: inline-block;
    color: #667eea;
    text-decoration: none;
    font-weight: 700;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.back-link:hover {
    color: #5568d3;
    transform: translateX(-5px);
}

.crypto-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 36px;
    border-radius: 20px;
    margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

.crypto-title-actions { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.header-icon-actions { display:flex; align-items:center; gap:10px; }
.icon-btn { 
    background: #f8fafc; 
    border: 1px solid #e2e8f0; 
    border-radius: 12px; 
    padding: 12px; 
    cursor: pointer; 
    color: #475569;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.icon-btn:hover { 
    background: #eef2ff; 
    border-color: #c7d2fe; 
    color: #4338ca;
    transform: scale(1.05);
}
.icon-btn.favorited {
    background: #fef3c7;
    border-color: #fbbf24;
    color: #d97706;
}
.price-inline { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; margin-top:14px; flex-wrap:wrap; }
.price-inline .price-number { display:flex; align-items:center; gap:10px; font-size:28px; font-weight:900; color:#0f172a; }
.price-inline .price-sub { display:flex; align-items:center; gap:10px; color:#64748b; font-weight:700; }
.mini-sparkline { display:inline-block; width:140px; height:28px; }
.spark-svg { width:100%; height:100%; }
.tabs-nav { 
    display: flex; 
    gap: 10px; 
    margin-top: 20px; 
    flex-wrap: wrap;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 4px;
}
.tab-btn { 
    background: transparent; 
    border: none;
    border-bottom: 3px solid transparent;
    padding: 12px 18px; 
    border-radius: 8px 8px 0 0; 
    font-weight: 700; 
    color: #64748b; 
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}
.tab-btn:hover {
    background: #f8fafc;
    color: #334155;
}
.tab-btn.active { 
    background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%);
    border-bottom-color: #6366f1;
    color: #4338ca;
    font-weight: 800;
}

[data-tab-content] {}
.markets-section, .news-section, .videos-section { 
    background: white; 
    padding: 32px; 
    border-radius: 20px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
    margin-bottom: 28px;
    border: 1px solid #e2e8f0;
}
.markets-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.markets-table { 
    width: 100%; 
    border-collapse: separate; 
    border-spacing: 0 10px;
    min-width: 600px;
}
.markets-table thead th { 
    text-align: right; 
    font-size: 13px; 
    color: #64748b; 
    font-weight: 800; 
    padding: 12px 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.markets-table tbody tr { 
    background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%); 
    transition: all 0.3s ease;
    border-radius: 12px;
}
.markets-table tbody tr:hover { 
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
    transform: translateX(-4px);
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
}
.markets-table td { 
    padding: 16px; 
    font-weight: 700; 
    color: #0f172a; 
    border-top: 1px solid #e2e8f0; 
    border-bottom: 1px solid #e2e8f0;
}
.markets-table tbody tr td:first-child {
    border-left: 1px solid #e2e8f0;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}
.markets-table tbody tr td:last-child {
    border-right: 1px solid #e2e8f0;
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}
.markets-table .ex-name { 
    display: flex; 
    align-items: center; 
    gap: 12px; 
}
.ex-logo { 
    width: 24px; 
    height: 24px; 
    border-radius: 50%;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}
.history-table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.history-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
    min-width: 500px;
}
.history-table thead th {
    text-align: right;
    font-size: 13px;
    color: #64748b;
    font-weight: 800;
    padding: 12px 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.history-table tbody tr {
    background: #fafafa;
    transition: all 0.3s ease;
}
.history-table tbody tr:hover {
    background: #eef2ff;
}
.history-table td {
    padding: 14px 16px;
    font-weight: 600;
    color: #334155;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}
.history-table tbody tr td:first-child {
    border-left: 1px solid #e2e8f0;
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
}
.history-table tbody tr td:last-child {
    border-right: 1px solid #e2e8f0;
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
}
.news-header { 
    display: flex; 
    align-items: center; 
    justify-content: space-between; 
    margin-bottom: 20px; 
}
.see-all-news { 
    color: #6366f1; 
    text-decoration: none; 
    font-weight: 800;
    font-size: 14px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.see-all-news:hover {
    color: #4338ca;
    transform: translateX(-4px);
}
.see-all-news::after {
    content: '←';
    font-size: 18px;
}
.history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 16px;
}
.news-cards { 
    display: grid; 
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
    gap: 20px; 
}
.news-card { 
    background: #ffffff; 
    border: 1px solid #e2e8f0; 
    border-radius: 16px; 
    overflow: hidden; 
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    cursor: pointer;
}
.news-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-color: #c7d2fe;
}
.news-thumb { 
    width: 100%; 
    height: 160px; 
    object-fit: cover; 
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
}
.news-meta { 
    display: flex; 
    gap: 10px; 
    align-items: center; 
    padding: 14px 16px 8px; 
}
.news-tag { 
    background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%); 
    color: #4338ca; 
    font-weight: 800; 
    border-radius: 8px; 
    padding: 4px 10px; 
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.news-time { 
    color: #64748b; 
    font-size: 12px; 
    font-weight: 600;
}
.news-title { 
    padding: 0 16px 16px; 
    font-size: 15px; 
    line-height: 1.6; 
    color: #1e293b;
    font-weight: 700;
}
.news-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}
.news-title a:hover {
    color: #6366f1;
}
.news-thumb-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%);
}
.news-icon {
    font-size: 48px;
}
.news-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    border: 2px dashed #e2e8f0;
}
.news-empty .empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.7;
}
.news-empty p {
    color: #64748b;
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.related-news-section {
    background: white;
    padding: 32px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 32px;
    border: 1px solid #e2e8f0;
}

.related-news-section .news-header h2 {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 24px 0;
}

.crypto-title-section {
    display: flex;
    align-items: center;
    gap: 20px;
}

.crypto-logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    border: 3px solid white;
}

.crypto-title-section h1 {
    font-size: 36px;
    font-weight: 900;
    margin: 0 0 8px 0;
    color: #0f172a;
}

.badges {
    display: flex;
    gap: 12px;
}

.badge {
    padding: 8px 18px;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.price-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.price-card.combined {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 32px;
}

.price-info-group {
    flex: 1;
}

.divider-vertical {
    width: 2px;
    height: 80px;
    background: linear-gradient(180deg, transparent, #e2e8f0, transparent);
}

.price-card.main .divider-vertical {
    background: linear-gradient(180deg, transparent, rgba(255,255,255,0.3), transparent);
}

.price-card {
    background: white;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e2e8f0;
}

.price-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    border-color: #c7d2fe;
}

.price-card.main {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
    color: white;
    border: none;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
}
.price-card.main:hover {
    box-shadow: 0 16px 40px rgba(99, 102, 241, 0.4);
}

.label {
    font-size: 13px;
    opacity: 0.85;
    margin-bottom: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-card.main .label {
    opacity: 0.95;
}

.price-big {
    font-size: 28px;
    font-weight: 900;
    margin-bottom: 8px;
    line-height: 1.2;
}

.price-card.combined .price-big {
    font-size: 26px;
}

.price-big .loading {
    font-size: 18px;
    opacity: 0.7;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.7; }
    50% { opacity: 0.3; }
}

.price-small {
    font-size: 20px;
    opacity: 0.9;
    margin-bottom: 12px;
    font-weight: 600;
}

.usdt-info {
    font-size: 12px;
    opacity: 0.8;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.2);
}

.value {
    font-size: 28px;
    font-weight: 900;
    color: #0f172a;
    margin-bottom: 8px;
}

.price-card.combined .value {
    font-size: 24px;
}

.value.green { color: #16c784; }
.value.red { color: #ea3943; }

.price-card.main .value {
    color: white;
}

.sub-changes {
    display: flex;
    gap: 12px;
    font-size: 13px;
    margin-top: 8px;
}

.sub-changes span {
    padding: 6px 12px;
    background: #f1f5f9;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
}

.sub-changes .green { color: #16c784; }
.sub-changes .red { color: #ea3943; }

.price-card.main .sub-changes span {
    background: rgba(255,255,255,0.15);
    color: white;
}

.price-card.main .sub-changes .green {
    color: #4ade80;
}

.price-card.main .sub-changes .red {
    color: #fca5a5;
}

.sub-info {
    font-size: 12px;
    color: #64748b;
    margin-top: 6px;
    font-weight: 600;
}

.price-card.main .sub-info {
    color: rgba(255,255,255,0.8);
}

/* اطلاعات اضافی */
.additional-stats {
    background: white;
    padding: 32px;
    border-radius: 20px;
    margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

.stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 18px;
}

.stat-box {
    padding: 24px;
    background: linear-gradient(135deg, #fafafa 0%, #f1f5f9 100%);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}
.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 8px;
    font-weight: 600;
}

.stat-value {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
}

.stat-change {
    font-size: 14px;
}

.stat-change.green { color: #16c784; }
.stat-change.red { color: #ea3943; }

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    margin: 8px 0;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    border-radius: 10px;
    transition: width 1s ease;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.stat-value small {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.charts-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.chart-box {
    background: white;
    padding: 32px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

.currency-toggle .btn,
.chart-controls .btn {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.currency-toggle .btn:hover,
.chart-controls .btn:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4338ca;
}

.currency-toggle .btn.active,
.chart-controls .btn.active {
    background: linear-gradient(135deg, #e0e7ff 0%, #ddd6fe 100%);
    border-color: #6366f1;
    color: #4338ca;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}

.chart-controls-wrapper {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.currency-toggle {
    display: flex;
    gap: 6px;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 12px;
}

.currency-toggle .toggle-currency {
    padding: 8px 16px;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 700;
    cursor: pointer;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.currency-toggle .toggle-currency:hover {
    color: #334155;
}

.currency-toggle .toggle-currency.active {
    background: white;
    color: #6366f1;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chart-range-buttons {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.chart-range-buttons button {
    padding: 8px 14px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    font-weight: 700;
    cursor: pointer;
    border-radius: 10px;
    transition: all 0.3s ease;
    font-size: 13px;
}

.chart-range-buttons button:hover {
    background: #f8fafc;
    border-color: #c7d2fe;
    color: #4338ca;
}

.chart-range-buttons button.active {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-color: #6366f1;
    color: white;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}

.tv-controls button.active {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border-color: #6366f1;
}

.chart-area {
    position: relative;
    min-height: 400px;
    height: 450px;
}

.tv-chart-container {
    height: 500px;
    border-radius: 12px;
    overflow: hidden;
    background: #fafafa;
}

.chart-loader {
    position: absolute;
    inset: 0;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.95);
    color: #475569;
    font-weight: 700;
    border-radius: 12px;
    gap: 16px;
    z-index: 10;
}

.loader-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e2e8f0;
    border-top-color: #6366f1;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.chart-box h2 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 24px 0;
    color: #0f172a;
}
.additional-stats h2,
.markets-section h2,
.news-section h2,
.videos-section h2,
.history-section h2,
.content-section h2 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 24px 0;
    color: #0f172a;
}
.converter-section h2 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 20px 0;
    color: #0f172a;
}

.content-section {
    background: white;
    padding: 36px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    line-height: 1.8;
}

.content-section h2 {
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 20px 0;
    color: #0f172a;
}

.content-section h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 24px 0 16px 0;
    color: #334155;
}

.content-section p {
    line-height: 1.9;
    color: #475569;
    margin-bottom: 18px;
    font-size: 15px;
}

.content-section ul {
    line-height: 2;
    padding-right: 24px;
}

.content-section ul li {
    margin-bottom: 8px;
}

.content-section blockquote {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-right: 4px solid #6366f1;
    padding: 20px 24px;
    margin: 24px 0;
    border-radius: 12px;
    font-style: italic;
}

.content-section a {
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.content-section a:hover {
    color: #4338ca;
    text-decoration: underline;
}

.content-section img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 20px 0;
}

@media (max-width: 1200px) {
    .container {
        padding: 0 20px;
    }
    .page-grid {
        grid-template-columns: 1fr 320px;
        gap: 24px;
    }
}

@media (max-width: 1024px) {
    .stat-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .page-grid { 
        grid-template-columns: 1fr; 
        gap: 20px;
    }
    .sidebar-col { 
        position: static;
        order: -1;
    }
    .price-section {
        grid-template-columns: 1fr;
    }
    .price-card.combined {
        padding: 24px;
    }
}

@media (max-width: 768px) {
    .single-crypto {
        padding: 20px 0;
    }
    .crypto-header {
        padding: 24px 20px;
        border-radius: 16px;
    }
    .crypto-title-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    .crypto-logo {
        width: 64px;
        height: 64px;
    }
    .crypto-title-section h1 {
        font-size: 24px;
    }
    .price-inline .price-number {
        font-size: 22px;
    }
    .price-section,
    .charts-section,
    .stat-row,
    .news-cards {
        grid-template-columns: 1fr;
    }
    .price-card.combined {
        flex-direction: column;
        align-items: stretch;
        gap: 20px;
        padding: 24px 20px;
    }
    .divider-vertical {
        width: 100%;
        height: 2px;
    }
    .price-big {
        font-size: 24px;
    }
    .converter-box {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .conv-swap {
        transform: rotate(90deg);
        justify-self: center;
    }
    .tabs-nav {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .tabs-nav::-webkit-scrollbar {
        display: none;
    }
    .tab-btn {
        white-space: nowrap;
        padding: 10px 16px;
        font-size: 14px;
    }
    .markets-table-wrapper {
        overflow-x: auto;
    }
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .chart-controls-wrapper {
        width: 100%;
        justify-content: space-between;
    }
    .chart-range-buttons {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 4px;
    }
    .chart-range-buttons::-webkit-scrollbar {
        display: none;
    }
    .chart-range-buttons button {
        white-space: nowrap;
        font-size: 12px;
        padding: 7px 12px;
    }
    .chart-area {
        height: 350px;
    }
    .tv-chart-container {
        height: 400px;
    }
    .related-news-section {
        padding: 24px 20px;
    }
}

@media (max-width: 480px) {
    .crypto-header {
        padding: 20px 16px;
    }
    .crypto-title-section h1 {
        font-size: 20px;
    }
    .badge {
        padding: 6px 12px;
        font-size: 12px;
    }
    .price-inline .price-number {
        font-size: 18px;
    }
    .price-card {
        padding: 20px;
    }
    .price-card.combined {
        padding: 20px 16px;
    }
    .price-card.combined .price-big {
        font-size: 22px;
    }
    .price-card.combined .value {
        font-size: 20px;
    }
    .value {
        font-size: 22px;
    }
    .currency-toggle {
        width: 100%;
    }
    .currency-toggle .toggle-currency {
        flex: 1;
        text-align: center;
    }
    .related-news-section {
        padding: 20px 16px;
    }
}
</style>

<?php get_footer(); ?>
