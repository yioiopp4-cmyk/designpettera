<?php
/**
 * Template Name: لیست کامل ارزها
 * 
 * @package CryptoSekhyab
 */

get_header('arzdigital');

$api = cg_api();
$usdt_rate = crypto_sekhyab_get_usdt_price();

// صفحه فعلی
$paged = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : (isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1);
$per_page = 100;

// دریافت ارزها با fallback امن
$cryptos = $api->get_coins_paginated($paged, $per_page);
if (!is_array($cryptos) || empty($cryptos)) {
    // تلاش دوم: از API سبک‌تر
    $cryptos = crypto_sekhyab_get_top_cryptos($per_page, $paged);
    if (!is_array($cryptos)) {
        $cryptos = array();
    }
}

// برآورد تعداد کل
$total_coins = max(1000, intval($api->get_total_coins_count()));
$total_pages = max(1, ceil($total_coins / $per_page));
?>

<main class="all-cryptos-page">
    
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo home_url(); ?>">خانه</a>
                <span>/</span>
                <span>لیست کامل ارزها</span>
            </div>
            
            <h1>📊 لیست کامل ارزهای دیجیتال</h1>
            <p>بیش از <?php echo number_format($total_coins); ?> ارز دیجیتال با قیمت لحظه‌ای</p>
        </div>
    </div>

    <!-- جدول -->
    <div class="cryptos-content">
        <div class="container">
            
            <?php if (!empty($cryptos)) : ?>
            <div class="cryptos-hero">
                <div class="hero-card">
                    <div class="hero-title">📈 بازار زنده ارزهای دیجیتال</div>
                    <div class="hero-sub">قیمت لحظه‌ای، تغییرات و مارکت کپ</div>
                </div>
                <div class="hero-actions">
                    <input type="text" id="search-input" placeholder="جستجوی نام یا نماد..." class="search-input" />
                    <select id="sort-select" class="sort-select">
                        <option value="rank">مرتب‌سازی: رتبه</option>
                        <option value="market_cap">مرتب‌سازی: مارکت کپ</option>
                        <option value="change_24h">مرتب‌سازی: تغییرات 24h</option>
                    </select>
                </div>
            </div>
            <div class="cryptos-table-full" id="cryptos-table">
                <div class="table-header">
                    <div class="th th-rank">#</div>
                    <div class="th th-name">نام</div>
                    <div class="th th-price">قیمت (تومان)</div>
                    <div class="th th-price-usd">قیمت (دلار)</div>
                    <div class="th th-change">1h</div>
                    <div class="th th-change">24h</div>
                    <div class="th th-change">7d</div>
                    <div class="th th-volume">حجم 24h</div>
                    <div class="th th-market">Market Cap</div>
                </div>
                
                <?php 
                foreach ($cryptos as $index => $crypto) :
                    if (!is_array($crypto)) continue;
                    
                    $crypto_id = isset($crypto['id']) ? $crypto['id'] : '';
                    $name = isset($crypto['name']) ? $crypto['name'] : 'Unknown';
                    $symbol = isset($crypto['symbol']) ? strtoupper($crypto['symbol']) : 'N/A';
                    $image = isset($crypto['image']) ? $crypto['image'] : '';
                    $price_usd = isset($crypto['current_price']) ? $crypto['current_price'] : 0;
                    $price_irr = $price_usd * $usdt_rate;
                    $change_1h = isset($crypto['price_change_percentage_1h_in_currency']) ? $crypto['price_change_percentage_1h_in_currency'] : 0;
                    $change_24h = isset($crypto['price_change_percentage_24h']) ? $crypto['price_change_percentage_24h'] : 0;
                    $change_7d = isset($crypto['price_change_percentage_7d_in_currency']) ? $crypto['price_change_percentage_7d_in_currency'] : 0;
                    $volume = isset($crypto['total_volume']) ? $crypto['total_volume'] : 0;
                    $market_cap = isset($crypto['market_cap']) ? $crypto['market_cap'] : 0;
                    
                    // استفاده از market_cap_rank برای نمایش رتبه واقعی
                    $market_cap_rank = isset($crypto['market_cap_rank']) ? intval($crypto['market_cap_rank']) : 0;
                    $display_rank = $market_cap_rank > 0 ? $market_cap_rank : ((int)(((int)$paged - 1) * (int)$per_page) + (int)$index + 1);
                    
                    // لینک
                    $crypto_link = '#';
                    if ($crypto_id) {
                        $posts = get_posts(array(
                            'post_type' => 'cryptocurrency',
                            'meta_key' => '_crypto_coingecko_id',
                            'meta_value' => $crypto_id,
                            'posts_per_page' => 1
                        ));
                        if (!empty($posts)) {
                            $crypto_link = get_permalink($posts[0]->ID);
                        }
                    }
                ?>
                    <a href="<?php echo esc_url($crypto_link); ?>" class="table-row" data-name="<?php echo esc_attr($name); ?>" data-symbol="<?php echo esc_attr($symbol); ?>" data-marketcap="<?php echo esc_attr($market_cap); ?>" data-change24="<?php echo esc_attr($change_24h); ?>">
                        <div class="td td-rank"><?php echo $display_rank; ?></div>
                        
                        <div class="td td-name">
                            <?php if ($image) : ?>
                                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy">
                            <?php endif; ?>
                            <div class="crypto-info">
                                <div class="name"><?php echo esc_html($name); ?></div>
                                <div class="symbol"><?php echo esc_html($symbol); ?></div>
                            </div>
                        </div>
                        
                        <div class="td td-price">
                            <div class="price-main"><?php echo number_format($price_irr, 0); ?></div>
                        </div>
                        
                        <div class="td td-price-usd">
                            $<?php echo number_format($price_usd, $price_usd < 1 ? 6 : 2); ?>
                        </div>
                        
                        <div class="td td-change">
                            <?php if ($change_1h != 0) : ?>
                            <span class="badge-change <?php echo $change_1h >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $change_1h >= 0 ? '▲' : '▼'; ?>
                                <?php echo number_format(abs($change_1h), 2); ?>%
                            </span>
                            <?php else : ?>
                            <span class="no-data">-</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="td td-change">
                            <span class="badge-change <?php echo $change_24h >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $change_24h >= 0 ? '▲' : '▼'; ?>
                                <?php echo number_format(abs($change_24h), 2); ?>%
                            </span>
                        </div>
                        
                        <div class="td td-change">
                            <?php if ($change_7d != 0) : ?>
                            <span class="badge-change <?php echo $change_7d >= 0 ? 'positive' : 'negative'; ?>">
                                <?php echo $change_7d >= 0 ? '▲' : '▼'; ?>
                                <?php echo number_format(abs($change_7d), 2); ?>%
                            </span>
                            <?php else : ?>
                            <span class="no-data">-</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="td td-volume">
                            <?php 
                            if ($volume >= 1000000000) {
                                echo '$' . number_format($volume / 1000000000, 2) . 'B';
                            } else if ($volume >= 1000000) {
                                echo '$' . number_format($volume / 1000000, 0) . 'M';
                            } else {
                                echo '$' . number_format($volume, 0);
                            }
                            ?>
                        </div>
                        
                        <div class="td td-market">
                            <?php 
                            if ($market_cap >= 1000000000) {
                                echo '$' . number_format($market_cap / 1000000000, 2) . 'B';
                            } else if ($market_cap >= 1000000) {
                                echo '$' . number_format($market_cap / 1000000, 0) . 'M';
                            } else {
                                echo '$' . number_format($market_cap, 0);
                            }
                            ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($total_pages > 1) : ?>
            <div class="pagination">
                <?php if ($paged > 1) : ?>
                    <a href="?pg=<?php echo $paged - 1; ?>" class="page-btn">
                        ← صفحه قبل
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php
                    $start_page = max(1, $paged - 2);
                    $end_page = min($total_pages, $paged + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="?pg=1" class="page-num">1</a>';
                        if ($start_page > 2) {
                            echo '<span class="dots">...</span>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active = $i == $paged ? 'active' : '';
                        echo '<a href="?pg=' . $i . '" class="page-num ' . $active . '">' . $i . '</a>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span class="dots">...</span>';
                        }
                        echo '<a href="?pg=' . $total_pages . '" class="page-num">' . $total_pages . '</a>';
                    }
                    ?>
                </div>
                
                <?php if ($paged < $total_pages) : ?>
                    <a href="?pg=<?php echo $paged + 1; ?>" class="page-btn">
                        صفحه بعد →
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php else : ?>
            <div class="no-data">
                <span>📊</span>
                <h3>در حال بارگذاری...</h3>
                <p>لطفاً چند لحظه صبر کنید</p>
            </div>
            <script>
            // تلاش سمت کاربر فقط برای رفرش در صورت خالی بودن (بدون بارگذاری بیشتر)
            (function(){
              var table = document.getElementById('cryptos-table');
              if (!table || !table.querySelector('.table-row')) {
                setTimeout(function(){ location.reload(); }, 1500);
              }
            })();
            </script>
            <?php endif; ?>
            
        </div>
    </div>

</main>

<style>
.all-cryptos-page {
    background: #f8fafc;
    min-height: 100vh;
}

.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 60px 0;
}

.breadcrumb {
    font-size: 14px;
    margin-bottom: 16px;
    opacity: 0.9;
}

.breadcrumb a {
    color: white;
    text-decoration: none;
}

.breadcrumb span {
    margin: 0 8px;
}

.page-header h1 {
    font-size: 42px;
    font-weight: 900;
    margin: 0 0 12px 0;
}

.page-header p {
    font-size: 18px;
    margin: 0;
    opacity: 0.9;
}

.cryptos-content {
    padding: 40px 0;
}

.cryptos-table-full {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.cryptos-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.hero-card {
    background: white;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.hero-title { font-size: 18px; font-weight: 800; color: #0f172a; }
.hero-sub { font-size: 13px; color: #64748b; margin-top: 4px; }

.hero-actions { display: flex; gap: 12px; align-items: center; }
.search-input {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
}
.sort-select {
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    background: white;
}

.load-more-wrapper { display: flex; justify-content: center; margin: 16px 0; }

.table-header {
    display: grid;
    grid-template-columns: 60px 2fr 1.5fr 1.2fr 90px 90px 90px 120px 140px;
    padding: 16px 20px;
    background: #f8fafc;
    font-weight: 700;
    font-size: 13px;
    color: #64748b;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
}

.table-row {
    display: grid;
    grid-template-columns: 60px 2fr 1.5fr 1.2fr 90px 90px 90px 120px 140px;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
    align-items: center;
}

.table-row:hover {
    background: #f8fafc;
}

.td-name {
    display: flex;
    align-items: center;
    gap: 12px;
}

.td-name img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.crypto-info .name {
    font-weight: 700;
    font-size: 15px;
    color: #0f172a;
}

.crypto-info .symbol {
    font-size: 13px;
    color: #64748b;
}

.price-main {
    font-weight: 700;
    font-size: 16px;
    color: #0f172a;
}

.td-price-usd {
    font-weight: 600;
    color: #64748b;
}

.badge-change {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
}

.badge-change.positive {
    background: rgba(22,199,132,0.1);
    color: #16c784;
}

.badge-change.negative {
    background: rgba(234,57,67,0.1);
    color: #ea3943;
}

.no-data {
    color: #cbd5e1;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    margin-top: 40px;
}

.page-btn {
    padding: 12px 24px;
    background: white;
    color: #667eea;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s;
}

.page-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.page-numbers {
    display: flex;
    gap: 8px;
}

.page-num {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #0f172a;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s;
}

.page-num:hover,
.page-num.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.dots {
    padding: 0 8px;
    color: #cbd5e1;
}

@media (max-width: 1024px) {
    .table-header,
    .table-row {
        grid-template-columns: 50px 2fr 1fr 1fr 80px 80px;
    }
    .td-change:nth-child(5),
    .td-change:nth-child(7),
    .td-volume {
        display: none;
    }
    .th:nth-child(5),
    .th:nth-child(7),
    .th:nth-child(8) {
        display: none;
    }
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 28px;
    }
    
    .table-header {
        display: none;
    }
    
    .table-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}
</style>

<?php get_footer(); ?>
