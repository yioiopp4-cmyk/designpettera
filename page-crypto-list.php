<?php
/**
 * Template Name: لیست ارزها (ساده)
 * 
 * @package CryptoSekhyab
 */

get_header('arzdigital');

$cryptos = crypto_sekhyab_get_top_cryptos(100);
$usdt_rate = crypto_sekhyab_get_usdt_price();

// چک کردن که $cryptos یک array است
if (!is_array($cryptos)) {
    $cryptos = array();
}
?>

<main id="main-content" class="site-main crypto-list-page">
    <div class="container">
        
        <div class="page-header">
            <h1 class="page-title">قیمت لحظه‌ای ۱۰۰ ارز برتر دیجیتال</h1>
            <div class="usdt-rate-badge">
                نرخ تتر: <strong><?php echo number_format($usdt_rate, 0); ?></strong> تومان
            </div>
        </div>

        <?php if (!empty($cryptos)) : ?>
            <div class="crypto-table-modern full-list">
                <div class="table-header">
                    <div class="th rank">#</div>
                    <div class="th name">نام</div>
                    <div class="th price-irr">قیمت (تومان)</div>
                    <div class="th change">24h</div>
                    <div class="th change">7d</div>
                    <div class="th volume">حجم 24h</div>
                    <div class="th marketcap">مارکت کپ</div>
                </div>
                
                <div class="table-body">
                    <?php foreach ($cryptos as $crypto) : 
                        if (!is_array($crypto)) continue;
                        
                        $current_price = isset($crypto['current_price']) ? floatval($crypto['current_price']) : 0;
                        $price_irr = $current_price * $usdt_rate;
                        $change_24h = isset($crypto['price_change_percentage_24h']) ? floatval($crypto['price_change_percentage_24h']) : 0;
                        $change_7d = isset($crypto['price_change_percentage_7d_in_currency']) ? floatval($crypto['price_change_percentage_7d_in_currency']) : 0;
                        $total_volume = isset($crypto['total_volume']) ? floatval($crypto['total_volume']) : 0;
                        $market_cap = isset($crypto['market_cap']) ? floatval($crypto['market_cap']) : 0;
                        
                        // پیدا کردن لینک صفحه ارز
                        $crypto_id = isset($crypto['id']) ? $crypto['id'] : '';
                        $crypto_link = '#';
                        if (!empty($crypto_id)) {
                            $crypto_posts = get_posts(array(
                                'post_type' => 'cryptocurrency',
                                'meta_key' => '_crypto_coingecko_id',
                                'meta_value' => $crypto_id,
                                'posts_per_page' => 1
                            ));
                            if (!empty($crypto_posts)) {
                                $crypto_link = get_permalink($crypto_posts[0]->ID);
                            }
                        }
                    ?>
                        <a href="<?php echo esc_url($crypto_link); ?>" class="table-row crypto-item crypto-link">
                            <div class="td rank">
                                <span class="rank-number"><?php echo esc_html($crypto['market_cap_rank'] ?? '-'); ?></span>
                            </div>
                            
                            <div class="td name">
                                <div class="crypto-info">
                                    <?php if (isset($crypto['image'])) : ?>
                                        <img src="<?php echo esc_url($crypto['image']); ?>" 
                                             alt="<?php echo esc_attr($crypto['name'] ?? ''); ?>" 
                                             class="crypto-logo"
                                             loading="lazy">
                                    <?php endif; ?>
                                    <div class="crypto-names">
                                        <span class="crypto-name"><?php echo esc_html($crypto['name'] ?? '-'); ?></span>
                                        <span class="crypto-symbol"><?php echo esc_html(strtoupper($crypto['symbol'] ?? '')); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="td price-irr">
                                <span class="price-tomani"><?php echo number_format($price_irr, 0); ?> تومان</span>
                            </div>
                            
                            <div class="td change">
                                <span class="change-percent <?php echo $change_24h >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $change_24h >= 0 ? '▲' : '▼'; ?>
                                    <?php echo number_format(abs($change_24h), 2); ?>%
                                </span>
                            </div>
                            
                            <div class="td change">
                                <?php if ($change_7d != 0) : ?>
                                    <span class="change-percent <?php echo $change_7d >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $change_7d >= 0 ? '▲' : '▼'; ?>
                                        <?php echo number_format(abs($change_7d), 2); ?>%
                                    </span>
                                <?php else : ?>
                                    <span class="no-data">-</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="td volume">
                                <span class="volume-text">
                                    <?php 
                                    if ($total_volume >= 1000000) {
                                        echo '$' . number_format($total_volume / 1000000, 1) . 'M';
                                    } else {
                                        echo '$' . number_format($total_volume, 0);
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="td marketcap">
                                <span class="marketcap-text">
                                    <?php 
                                    if ($market_cap >= 1000000000) {
                                        echo '$' . number_format($market_cap / 1000000000, 2) . 'B';
                                    } else if ($market_cap >= 1000000) {
                                        echo '$' . number_format($market_cap / 1000000, 0) . 'M';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="loading-state">
                <div class="spinner"></div>
                <p>در حال بارگذاری لیست ارزها...</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<style>
.crypto-list-page {
    padding: 60px 0;
    background: #f8fafc;
}

.page-header {
    text-align: center;
    margin-bottom: 48px;
}

.page-title {
    font-size: 36px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 16px;
}

.usdt-rate-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 24px;
    font-size: 16px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.full-list .table-header {
    grid-template-columns: 60px 2.5fr 1.2fr 100px 100px 120px 140px;
}

.full-list .table-row {
    grid-template-columns: 60px 2.5fr 1.2fr 100px 100px 120px 140px;
}

.crypto-link {
    text-decoration: none;
    color: inherit;
    display: grid;
}

.crypto-link:hover .crypto-name {
    color: #667eea;
}

@media (max-width: 1200px) {
    .full-list .table-header,
    .full-list .table-row {
        grid-template-columns: 50px 2fr 1fr 90px 90px 110px 130px;
        font-size: 13px;
    }
}

@media (max-width: 768px) {
    .full-list .table-header {
        display: none;
    }
    
    .full-list .table-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .page-title {
        font-size: 24px;
    }
}
</style>

<?php get_footer(); ?>
