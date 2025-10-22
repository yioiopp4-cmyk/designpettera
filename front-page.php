<?php
/**
 * صفحه اصلی - استایل Arzdigital
 * 
 * @package CryptoSekhyab
 */

get_header('arzdigital');

// انتخاب منبع API
$api_source = get_option('crypto_api_source', 'coinmarketcap');

// دریافت داده‌ها بر اساس منبع
$top_cryptos = array();
$trending = array();
$global_data = array(
    'active_cryptocurrencies' => 10000,
    'total_market_cap' => array('usd' => 0),
    'total_volume' => array('usd' => 0),
    'market_cap_percentage' => array('btc' => 0),
);

try {
    if ($api_source == 'coinmarketcap') {
        $cmc = cmc_api();
        $global_data_raw = $cmc->get_global_metrics();
        $top_raw = $cmc->get_listings(1, 100);
        $trending_raw = $cmc->get_trending();
        
        if (is_array($top_raw)) {
            foreach ($top_raw as $coin) {
                if (!is_array($coin)) continue;
                $normalized = $cmc->normalize_coin_data($coin);
                if ($normalized && is_array($normalized)) {
                    $normalized['cmc_id'] = isset($coin['id']) ? $coin['id'] : 0;
                    if (isset($coin['id'])) {
                        $normalized['image'] = $cmc->get_logo_url($coin['id']);
                    }
                    $top_cryptos[] = $normalized;
                }
            }
        }
        
        if (is_array($trending_raw)) {
            foreach ($trending_raw as $coin) {
                if (!is_array($coin)) continue;
                $normalized = $cmc->normalize_coin_data($coin);
                if ($normalized && is_array($normalized)) {
                    $normalized['cmc_id'] = isset($coin['id']) ? $coin['id'] : 0;
                    if (isset($coin['id'])) {
                        $normalized['image'] = $cmc->get_logo_url($coin['id']);
                    }
                    $trending[] = array('item' => $normalized);
                }
            }
        }
        
        if (is_array($global_data_raw)) {
            $global_data = array(
                'active_cryptocurrencies' => isset($global_data_raw['active_cryptocurrencies']) ? $global_data_raw['active_cryptocurrencies'] : 10000,
                'total_market_cap' => array('usd' => isset($global_data_raw['quote']['USD']['total_market_cap']) ? $global_data_raw['quote']['USD']['total_market_cap'] : 0),
                'total_volume' => array('usd' => isset($global_data_raw['quote']['USD']['total_volume_24h']) ? $global_data_raw['quote']['USD']['total_volume_24h'] : 0),
                'market_cap_percentage' => array('btc' => isset($global_data_raw['btc_dominance']) ? $global_data_raw['btc_dominance'] : 0),
            );
        }
    } else {
        $api = cg_api();
        $global_data = $api->get_global_market_data();
        $top_cryptos = $api->get_coins_paginated(1, 100);
        $trending = $api->get_trending_coins();
    }
} catch (Exception $e) {
    // در صورت خطا
}

// دریافت نرخ تتر واقعی
$usdt_rate = crypto_sekhyab_get_usdt_price();
?>
<?php
// مقداردهی متغیرهای Top Toolbar از داده‌های global
$total_market_cap = isset($global_data['total_market_cap']['usd']) ? $global_data['total_market_cap']['usd'] : 0;
$btc_dominance = isset($global_data['market_cap_percentage']['btc']) ? $global_data['market_cap_percentage']['btc'] : 0;
$active_cryptos = isset($global_data['active_cryptocurrencies']) ? $global_data['active_cryptocurrencies'] : 10000;
?>

<main class="arzdigital-main">
    <!-- Dashboard Section -->
    <section class="dashboard-section">
        <div class="dashboard-container">
            
            <!-- بخش بالایی -->
            <div class="top-section-grid">
                
                <!-- اخبار ویژه -->
                <div class="card featured-stories">
                    <div class="card-header">
                        <h3>📰 اخبار ویژه</h3>
                        <button class="urgent-news-btn" onclick="window.location.href='<?php echo home_url('/news'); ?>'">اخبار فوری</button>
                    </div>
                    <div class="card-body">
                        <div class="featured-slider">
                            <?php
                            $featured_slides = get_posts(array(
                                'posts_per_page' => 10,
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'meta_query' => array(
                                    array(
                                        'key' => 'breaking_news',
                                        'value' => '1',
                                        'compare' => '='
                                    )
                                )
                            ));
                            
                            $slides_count = count($featured_slides);
                            $slide_index = 0;
                            foreach ($featured_slides as $slide_post) :
                                $active_class = $slide_index === 0 ? 'active' : '';
                            ?>
                                <div class="featured-slide <?php echo $active_class; ?>">
                                    <div class="image-placeholder">
                                        <?php if (has_post_thumbnail($slide_post->ID)) : ?>
                                            <img src="<?php echo get_the_post_thumbnail_url($slide_post->ID, 'large'); ?>" alt="" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">
                                        <?php else : ?>
                                            <i class="fa-solid fa-newspaper"></i>
                                        <?php endif; ?>
                                        <!-- Progress Bar -->
                                        <div class="slide-progress-bar">
                                            <div class="slide-progress-fill"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                $slide_index++;
                            endforeach; 
                            ?>
                        </div>
                        <?php wp_reset_postdata(); ?>
                        <div class="featured-news-list <?php echo $slides_count > 3 ? 'auto-scroll' : ''; ?>" data-count="<?php echo $slides_count; ?>">
                            <?php 
                            $news_index = 0;
                            foreach ($featured_slides as $news_item) : 
                                $is_active = $news_index === 0 ? 'active-news' : '';
                            ?>
                                <div class="news-item <?php echo $is_active; ?>" data-slide="<?php echo $news_index; ?>">
                                    <p><a href="<?php echo get_permalink($news_item->ID); ?>"><?php echo esc_html($news_item->post_title); ?></a></p>
                                </div>
                            <?php 
                                $news_index++;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>

                <!-- ارزهای ترند -->
                <div class="card trend-values">
                    <div class="card-header">
                        <h3>🔥 ارزهای ترند</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $trending_display = array_slice($top_cryptos, 0, 6);
                        foreach ($trending_display as $coin) :
                            $price_usd = isset($coin['current_price']) ? $coin['current_price'] : 0;
                            $price_irr = $price_usd * $usdt_rate;
                            $change_24h = isset($coin['price_change_percentage_24h']) ? $coin['price_change_percentage_24h'] : 0;
                            $change_class = $change_24h >= 0 ? 'up' : 'down';
                            $change_icon = $change_24h >= 0 ? 'fa-caret-up' : 'fa-caret-down';
                        ?>
                            <div class="coin-item">
                                <div class="coin-info">
                                    <img src="<?php echo esc_url($coin['image']); ?>" alt="<?php echo esc_attr($coin['name']); ?>" class="coin-logo-img">
                                    <span class="coin-name"><?php echo esc_html($coin['name']); ?></span>
                                </div>
                                <div class="coin-price-change">
                                    <div class="price">$<?php echo number_format($price_usd, $price_usd < 1 ? 4 : 2); ?></div>
                                    <div class="price-fa"><?php echo number_format($price_irr, 0); ?></div>
                                    <div class="change <?php echo $change_class; ?>">
                                        <?php echo number_format(abs($change_24h), 2); ?>% 
                                        <i class="fas <?php echo $change_icon; ?>"></i>
                                    </div>
                                </div>
                                <div class="mini-chart">
                                    <?php
                                    $sparkline = isset($coin['sparkline_in_7d']['price']) ? $coin['sparkline_in_7d']['price'] : array();
                                    if (!empty($sparkline) && is_array($sparkline)) :
                                        $min_price = min($sparkline);
                                        $max_price = max($sparkline);
                                        $price_range = $max_price - $min_price;
                                        $points = array();
                                        $width = 60;
                                        $height = 20;
                                        $step = $width / (count($sparkline) - 1);
                                        
                                        foreach ($sparkline as $i => $price) {
                                            $x = $i * $step;
                                            $y = $price_range > 0 ? $height - (($price - $min_price) / $price_range * $height) : $height / 2;
                                            $points[] = "$x,$y";
                                        }
                                        $points_str = implode(' ', $points);
                                    ?>
                                        <svg width="60" height="20" viewBox="0 0 60 20" style="display:block;">
                                            <polyline points="<?php echo esc_attr($points_str); ?>" 
                                                      fill="none" 
                                                      stroke="<?php echo $change_24h >= 0 ? '#2ecc71' : '#e74c3c'; ?>" 
                                                      stroke-width="1.5"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="slider-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>
                
            </div>

            <!-- بخش پایینی -->
            <div class="bottom-section-grid">
                
                <!-- بیشترین افت -->
                <?php
                $sorted_by_change = $top_cryptos;
                usort($sorted_by_change, function($a, $b) {
                    $change_a = isset($a['price_change_percentage_24h']) ? $a['price_change_percentage_24h'] : 0;
                    $change_b = isset($b['price_change_percentage_24h']) ? $b['price_change_percentage_24h'] : 0;
                    return $change_a <=> $change_b;
                });
                $top_3_losers = array_slice($sorted_by_change, 0, 3);
                ?>
                <div class="card top-losers">
                    <div class="card-header">
                        <h3>📉 بیشترین افت</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $rank = 1;
                        foreach ($top_3_losers as $loser) : 
                            $change = isset($loser['price_change_percentage_24h']) ? $loser['price_change_percentage_24h'] : 0;
                            if ($change >= 0) continue;
                            
                            // پیدا کردن لینک ارز
                            $crypto_id = isset($loser['id']) ? $loser['id'] : '';
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
                            <div class="item clickable-item" onclick="window.location.href='<?php echo esc_url($crypto_link); ?>'" style="cursor: pointer;">
                                <span class="rank"><?php echo $rank; ?></span>
                                <img src="<?php echo esc_url($loser['image']); ?>" alt="<?php echo esc_attr($loser['name']); ?>" class="coin-icon-small">
                                <div class="coin-name-symbol">
                                    <?php echo esc_html($loser['name']); ?> 
                                    <span class="symbol"><?php echo esc_html(strtoupper($loser['symbol'])); ?></span>
                                </div>
                                <div class="change-percent down">
                                    <?php echo number_format(abs($change), 2); ?>% 
                                    <i class="fas fa-caret-down"></i>
                                </div>
                            </div>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </div>
                    <div class="slider-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                    </div>
                </div>

                <!-- بیشترین رشد -->
                <?php
                $top_3_gainers = array_slice(array_reverse($sorted_by_change), 0, 3);
                ?>
                <div class="card top-gainers">
                    <div class="card-header">
                        <h3>📈 بیشترین رشد</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $rank = 1;
                        foreach ($top_3_gainers as $gainer) : 
                            $change = isset($gainer['price_change_percentage_24h']) ? $gainer['price_change_percentage_24h'] : 0;
                            if ($change <= 0) continue;
                            
                            // پیدا کردن لینک ارز
                            $crypto_id = isset($gainer['id']) ? $gainer['id'] : '';
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
                            <div class="item clickable-item" onclick="window.location.href='<?php echo esc_url($crypto_link); ?>'" style="cursor: pointer;">
                                <span class="rank"><?php echo $rank; ?></span>
                                <img src="<?php echo esc_url($gainer['image']); ?>" alt="<?php echo esc_attr($gainer['name']); ?>" class="coin-icon-small">
                                <div class="coin-name-symbol">
                                    <?php echo esc_html($gainer['name']); ?> 
                                    <span class="symbol"><?php echo esc_html(strtoupper($gainer['symbol'])); ?></span>
                                </div>
                                <div class="change-percent up">
                                    <?php echo number_format(abs($change), 2); ?>% 
                                    <i class="fas fa-caret-up"></i>
                                </div>
                            </div>
                        <?php 
                            $rank++;
                        endforeach; 
                        ?>
                    </div>
                    <div class="slider-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                    </div>
                </div>

                <!-- آمار -->
                <div class="stats-grid-container">
                    <div class="stat-box fear-greed-box">
                        <h3>شاخص ترس و طمع</h3>
                        <div class="fear-greed-gauge">
                            <div class="gauge-bars">
                                <div class="gauge-bar extreme-fear"></div>
                                <div class="gauge-bar fear"></div>
                                <div class="gauge-bar neutral"></div>
                                <div class="gauge-bar greed"></div>
                                <div class="gauge-bar extreme-greed"></div>
                            </div>
                            <div class="gauge-pointer" style="transform: rotate(<?php echo (42 * 1.8) - 90; ?>deg);">
                                <div class="pointer-arrow"></div>
                            </div>
                            <div class="gauge-value">42</div>
                            <div class="gauge-label">😨 ترس</div>
                        </div>
                    </div>
                    <div class="stat-box market-cap-box">
                        <h3>💰 ارزش بازار</h3>
                        <div class="stat-value down">
                            $<?php echo number_format($total_market_cap / 1000000000000, 2); ?>T
                        </div>
                        <div class="mini-trend-chart">
                            <svg width="100%" height="30" viewBox="0 0 100 30" preserveAspectRatio="none">
                                <polyline points="0,20 20,15 40,18 60,12 80,16 100,10" 
                                          fill="none" 
                                          stroke="#e74c3c" 
                                          stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="stat-change down">-3.15%</div>
                    </div>
                    <div class="stat-box tether-box">
                        <h3>💵 قیمت تتر</h3>
                        <div class="stat-value">
                            <?php echo number_format($usdt_rate); ?>
                        </div>
                        <div class="mini-trend-chart">
                            <svg width="100%" height="30" viewBox="0 0 100 30" preserveAspectRatio="none">
                                <polyline points="0,25 20,22 40,20 60,18 80,20 100,15" 
                                          fill="none" 
                                          stroke="#e74c3c" 
                                          stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="stat-change down">-1.10%</div>
                    </div>
                    <div class="stat-box dominance-box">
                        <h3>👑 دامیننس</h3>
                        <div class="dominance-item">
                            <img src="https://cryptologos.cc/logos/bitcoin-btc-logo.png" alt="BTC" class="coin-icon-mini">
                            <span>BTC</span>
                            <strong><?php echo number_format($btc_dominance, 1); ?>%</strong>
                        </div>
                        <div class="dominance-item">
                            <img src="https://cryptologos.cc/logos/ethereum-eth-logo.png" alt="ETH" class="coin-icon-mini">
                            <span>ETH</span>
                            <strong>12.7%</strong>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- OLD SECTION REMOVED -->
    <section class="hero-content-section-old" style="display:none;">
        <div class="container">
            <div class="hero-grid-3col">
                <!-- Right Sidebar: Trending Coins -->
                <div class="trending-sidebar-old">
                    <div class="trending-box">
                        <h3 class="sidebar-title">ارزهای ترند</h3>
                        
                        <?php
                        $trending_coins = array_slice($top_cryptos, 0, 5);
                        foreach ($trending_coins as $coin) :
                            $price_usd = isset($coin['current_price']) ? $coin['current_price'] : 0;
                            $price_irr = $price_usd * $usdt_rate;
                            $change_24h = isset($coin['price_change_percentage_24h']) ? $coin['price_change_percentage_24h'] : 0;
                            $sparkline = isset($coin['sparkline_in_7d']['price']) ? $coin['sparkline_in_7d']['price'] : array();
                        ?>
                        <div class="trending-coin-card">
                            <div class="coin-top">
                                <img src="<?php echo esc_url($coin['image']); ?>" alt="" class="coin-icon">
                                <div class="coin-name-symbol">
                                    <div class="coin-name-ar"><?php echo esc_html($coin['name']); ?></div>
                                    <div class="coin-price-toman"><?php echo number_format($price_irr, 0); ?></div>
                                    <div class="coin-price-rial">ت <?php echo number_format($price_usd, $price_usd < 1 ? 4 : 0); ?></div>
                                </div>
                            </div>
                            <div class="coin-bottom">
                                <?php if (!empty($sparkline) && is_array($sparkline)) :
                                    $min_price = min($sparkline);
                                    $max_price = max($sparkline);
                                    $price_range = $max_price - $min_price;
                                    $points = array();
                                    $width = 100;
                                    $height = 40;
                                    $step = $width / (count($sparkline) - 1);
                                    
                                    foreach ($sparkline as $i => $price) {
                                        $x = $i * $step;
                                        $y = $price_range > 0 ? $height - (($price - $min_price) / $price_range * $height) : $height / 2;
                                        $points[] = "$x,$y";
                                    }
                                    $points_str = implode(' ', $points);
                                ?>
                                <svg width="100" height="40" viewBox="0 0 100 40" class="trend-sparkline">
                                    <polyline points="<?php echo esc_attr($points_str); ?>" 
                                              fill="none" 
                                              stroke="<?php echo $change_24h >= 0 ? '#ea3943' : '#16c784'; ?>" 
                                              stroke-width="2"/>
                                </svg>
                                <?php endif; ?>
                                <div class="trend-rank">1</div>
                            </div>
                            <div class="coin-change-percent <?php echo $change_24h >= 0 ? 'negative' : 'positive'; ?>">
                                <?php echo $change_24h >= 0 ? '↓' : '↑'; ?> <?php echo number_format(abs($change_24h), 2); ?>%
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Center: Main News Card + Stats Grid -->
                <div class="center-content">
                    <!-- Main News Card -->
                    <?php
                    $featured_posts = get_posts(array(
                        'posts_per_page' => 1,
                        'post_status' => 'publish',
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'meta_query' => array(
                            array(
                                'key' => 'breaking_news',
                                'value' => '1',
                                'compare' => '='
                            )
                        )
                    ));
                    
                    if (!empty($featured_posts)) :
                        $main_post = $featured_posts[0];
                    ?>
                    <div class="main-news-card">
                        <span class="news-badge">Markets</span>
                        <h2 class="news-title">
                            <a href="<?php echo get_permalink($main_post->ID); ?>">
                                <?php echo esc_html($main_post->post_title); ?>
                            </a>
                        </h2>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats Cards Grid -->
                    <div class="stats-grid-2x3">
                        <!-- Row 1 -->
                        <!-- بیشترین رشد -->
                        <?php if (!empty($top_cryptos)) : 
                            $sorted_by_change = $top_cryptos;
                            usort($sorted_by_change, function($a, $b) {
                                $change_a = isset($a['price_change_percentage_24h']) ? $a['price_change_percentage_24h'] : 0;
                                $change_b = isset($b['price_change_percentage_24h']) ? $b['price_change_percentage_24h'] : 0;
                                return $change_b <=> $change_a;
                            });
                            $top_gainer = $sorted_by_change[0];
                        ?>
                        <div class="stat-box gainer-box">
                            <h4>بیشترین رشد</h4>
                            <div class="stat-content">
                                <div class="crypto-info-stat">
                                    <img src="<?php echo esc_url($top_gainer['image']); ?>" alt="">
                                    <div>
                                        <div class="crypto-name-stat"><?php echo esc_html($top_gainer['name']); ?></div>
                                        <div class="crypto-symbol-stat"><?php echo esc_html(strtoupper($top_gainer['symbol'])); ?></div>
                                    </div>
                                </div>
                                <div class="percent-change positive">
                                    ↑ <?php echo number_format($top_gainer['price_change_percentage_24h'], 2); ?>%
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- ارزش بازار -->
                        <div class="stat-box market-box">
                            <h4>ارزش بازار</h4>
                            <div class="stat-content">
                                <div class="big-number">
                                    <?php 
                                    if ($total_market_cap >= 1000000000000) {
                                        echo number_format($total_market_cap / 1000000000000, 2);
                                    } else {
                                        echo number_format($total_market_cap / 1000000000, 0);
                                    }
                                    ?>
                                </div>
                                <div class="unit-label">ت</div>
                                <div class="percent-change positive">↑ ۳.۱۵%</div>
                            </div>
                        </div>
                        
                        <!-- شاخص ترس و طمع -->
                        <div class="stat-box fear-box">
                            <h4>شاخص ترس و طمع</h4>
                            <div class="stat-content">
                                <div class="gauge-container">
                                    <svg viewBox="0 0 100 60" class="gauge">
                                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#22c55e" stroke-width="8"/>
                                        <path d="M 90 50 A 40 40 0 0 1 50 10" fill="none" stroke="#eab308" stroke-width="8"/>
                                        <path d="M 50 10 A 40 40 0 0 1 10 50" fill="none" stroke="#ef4444" stroke-width="8"/>
                                        <line x1="50" y1="50" x2="50" y2="20" stroke="#1e293b" stroke-width="2"/>
                                    </svg>
                                    <div class="gauge-number">۴۷</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Row 2 -->
                        <!-- دامیننس -->
                        <div class="stat-box dominance-box">
                            <h4>دامیننس</h4>
                            <div class="stat-content">
                                <div class="dom-item">
                                    <img src="<?php echo !empty($top_cryptos[0]['image']) ? esc_url($top_cryptos[0]['image']) : ''; ?>" alt="BTC">
                                    <span><?php echo number_format($btc_dominance, 1); ?>% BTC</span>
                                </div>
                                <div class="dom-item">
                                    <img src="<?php echo !empty($top_cryptos[1]['image']) ? esc_url($top_cryptos[1]['image']) : ''; ?>" alt="ETH">
                                    <span>۱۲.۷% ETH</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- قیمت تتر -->
                        <div class="stat-box tether-box">
                            <h4>قیمت تتر</h4>
                            <div class="stat-content">
                                <div class="big-number"><?php echo number_format($usdt_rate); ?></div>
                                <div class="unit-label">ت</div>
                                <div class="percent-change positive">↑ ۱.۱۰%</div>
                            </div>
                        </div>
                        
                        <!-- بیشترین افت -->
                        <?php if (!empty($sorted_by_change)) : 
                            $top_loser = array_reverse($sorted_by_change)[0];
                        ?>
                        <div class="stat-box loser-box">
                            <h4>بیشترین افت</h4>
                            <div class="stat-content">
                                <div class="crypto-info-stat">
                                    <img src="<?php echo esc_url($top_loser['image']); ?>" alt="">
                                    <div>
                                        <div class="crypto-name-stat"><?php echo esc_html($top_loser['name']); ?></div>
                                        <div class="crypto-symbol-stat"><?php echo esc_html(strtoupper($top_loser['symbol'])); ?></div>
                                    </div>
                                </div>
                                <div class="percent-change negative">
                                    ↓ <?php echo number_format(abs($top_loser['price_change_percentage_24h']), 2); ?>%
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Left: Featured Stories -->
                <div class="featured-sidebar">
                    <div class="featured-box">
                        <h3 class="featured-title">Featured Stories</h3>
                        
                        <?php
                        $featured_articles = get_posts(array(
                            'posts_per_page' => 1,
                            'post_status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'offset' => 1,
                            'meta_query' => array(
                                array(
                                    'key' => 'breaking_news',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            )
                        ));
                        
                        if (!empty($featured_articles)) :
                            $featured_post = $featured_articles[0];
                        ?>
                        <div class="featured-article">
                            <?php if (has_post_thumbnail($featured_post->ID)) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url($featured_post->ID, 'large'); ?>" alt="" class="featured-img">
                            <?php else : ?>
                                <img src="https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=800&h=500&fit=crop" alt="" class="featured-img">
                            <?php endif; ?>
                            <div class="featured-content">
                                <span class="featured-badge">Markets</span>
                                <h4 class="featured-article-title">
                                    <a href="<?php echo get_permalink($featured_post->ID); ?>">
                                        <?php echo esc_html($featured_post->post_title); ?>
                                    </a>
                                </h4>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Additional News Items -->
                        <?php
                        $more_news = get_posts(array(
                            'posts_per_page' => 3,
                            'post_status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'offset' => 2,
                            'meta_query' => array(
                                array(
                                    'key' => 'breaking_news',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            )
                        ));
                        
                        foreach ($more_news as $news_item) :
                        ?>
                        <div class="mini-news-item">
                            <span class="mini-badge">Markets</span>
                            <h5 class="mini-news-title">
                                <a href="<?php echo get_permalink($news_item->ID); ?>">
                                    <?php echo esc_html($news_item->post_title); ?>
                                </a>
                            </h5>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Crypto Table -->
    <section class="crypto-table-section">
        <div class="container">
            <div class="section-header">
                <h2>قیمت لحظه‌ای ارزهای دیجیتال</h2>
                <div class="header-actions">
                    <div class="view-tabs">
                        <button class="tab-btn active" data-tab="all">همه</button>
                        <button class="tab-btn" data-tab="favorites">محبوب</button>
                        <?php if (get_option('crypto_sekhyab_show_defi_tab', '1') === '1') : ?>
                        <button class="tab-btn" data-tab="defi">دیفای</button>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo home_url('/all-cryptocurrencies'); ?>" class="view-all-link">
                        مشاهده همه
                        <i class="icon">←</i>
                    </a>
                </div>
            </div>
            
            <div class="crypto-table-wrapper">
                <table class="crypto-table">
                    <thead>
                        <tr>
                            <th class="rank-col sortable" data-sort="rank">#</th>
                            <th class="name-col sortable" data-sort="name">نام</th>
                            <th class="price-col sortable" data-sort="price">قیمت (تومان)</th>
                            <th class="price-usd-col sortable" data-sort="price_usd">قیمت (دلار)</th>
                            <th class="change-col sortable" data-sort="change_24h">تغییر 24h</th>
                            <th class="change-col sortable" data-sort="change_7d">هفتگی %</th>
                            <th class="change-col sortable" data-sort="change_30d">ماهانه %</th>
                            <th class="volume-col sortable" data-sort="volume">حجم 24h</th>
                            <th class="marketcap-col sortable" data-sort="marketcap">ارزش بازار</th>
                            <th class="chart-col">نمودار 7 روز</th>
                            <?php if (get_option('crypto_sekhyab_show_buy_button', '0') === '1') : ?>
                            <th class="buy-col"></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $display_count = 20;
                        // دریافت تنظیمات دکمه خرید
                        $show_buy_button = get_option('crypto_sekhyab_show_buy_button', '0') === '1';
                        $default_buy_link = get_option('crypto_sekhyab_default_buy_link', 'https://binance.com');
                        $custom_buy_links_raw = get_option('crypto_sekhyab_custom_buy_links', '');
                        
                        // پردازش لینک‌های مخصوص
                        $custom_buy_links = array();
                        if (!empty($custom_buy_links_raw)) {
                            $lines = explode("\n", $custom_buy_links_raw);
                            foreach ($lines as $line) {
                                $parts = explode('|', trim($line));
                                if (count($parts) === 2) {
                                    $custom_buy_links[strtolower(trim($parts[0]))] = trim($parts[1]);
                                }
                            }
                        }
                        
                        foreach (array_slice($top_cryptos, 0, $display_count) as $index => $crypto) :
                            $price_usd = isset($crypto['current_price']) ? $crypto['current_price'] : 0;
                            $price_irr = $price_usd * $usdt_rate; // محاسبه صحیح قیمت تومانی
                            $change_24h = isset($crypto['price_change_percentage_24h']) ? $crypto['price_change_percentage_24h'] : 0;
                            $change_7d = isset($crypto['price_change_percentage_7d_in_currency']) ? $crypto['price_change_percentage_7d_in_currency'] : 0;
                            $change_30d = isset($crypto['price_change_percentage_30d_in_currency']) ? $crypto['price_change_percentage_30d_in_currency'] : 0;
                            $volume = isset($crypto['total_volume']) ? $crypto['total_volume'] : 0;
                            $market_cap = isset($crypto['market_cap']) ? $crypto['market_cap'] : 0;
                            $symbol = isset($crypto['symbol']) ? strtoupper($crypto['symbol']) : '';
                            
                            // تعیین لینک خرید
                            $buy_link = $default_buy_link;
                            if (isset($custom_buy_links[strtolower($symbol)])) {
                                $buy_link = $custom_buy_links[strtolower($symbol)];
                            } else {
                                $buy_link = str_replace('{symbol}', $symbol, $default_buy_link);
                            }
                            
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
                            <tr class="crypto-row" 
                                data-rank="<?php echo intval($index) + 1; ?>"
                                data-price="<?php echo $price_usd; ?>"
                                data-change-24h="<?php echo $change_24h; ?>"
                                data-change-7d="<?php echo $change_7d; ?>"
                                data-change-30d="<?php echo $change_30d; ?>"
                                data-volume="<?php echo $volume; ?>"
                                data-marketcap="<?php echo $market_cap; ?>"
                                onclick="window.location.href='<?php echo esc_url($crypto_link); ?>'" 
                                style="cursor: pointer;">

                                <td class="rank-col">
                                    <div class="rank-number"><?php echo intval($index) + 1; ?></div>
                                </td>
                                <td class="name-col">
                                    <div class="crypto-name-wrapper">
                                        <img src="<?php echo esc_url($crypto['image'] ?? 'default-logo.png'); ?>" alt="" class="crypto-logo">
                                     <div class="name-info">
                                        <div class="name"><?php echo esc_html($crypto['name'] ?? 'نامشخص'); ?></div>
                                        <div class="symbol"><?php echo esc_html(strtoupper($crypto['symbol'] ?? '')); ?></div>
                                      </div>
                                    </div>

                                </td>
                                <td class="price-col">
                                    <div class="price-irr"><?php echo number_format($price_irr, 0); ?> <span class="currency-label">تومان</span></div>
                                </td>
                                <td class="price-usd-col">
                                    <div class="price-usd">$<?php echo number_format($price_usd, $price_usd < 1 ? 6 : 2); ?></div>
                                </td>
                                <td class="change-col">
                                    <div class="change-badge <?php echo $change_24h >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $change_24h >= 0 ? '↑' : '↓'; ?>
                                        <?php echo number_format(abs($change_24h), 2); ?>%
                                    </div>
                                </td>
                                <td class="change-col">
                                    <div class="change-badge <?php echo $change_7d >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $change_7d >= 0 ? '↑' : '↓'; ?>
                                        <?php echo number_format(abs($change_7d), 2); ?>%
                                    </div>
                                </td>
                                <td class="change-col">
                                    <div class="change-badge <?php echo $change_30d >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $change_30d >= 0 ? '↑' : '↓'; ?>
                                        <?php echo number_format(abs($change_30d), 2); ?>%
                                    </div>
                                </td>
                                <td class="volume-col">
                                    <?php 
                                    if ($volume >= 1000000000) {
                                        echo '$' . number_format($volume / 1000000000, 2) . 'B';
                                    } else {
                                        echo '$' . number_format($volume / 1000000, 0) . 'M';
                                    }
                                    ?>
                                </td>
                                <td class="marketcap-col">
                                    <?php 
                                    if ($market_cap >= 1000000000) {
                                        echo '$' . number_format($market_cap / 1000000000, 2) . 'B';
                                    } else {
                                        echo '$' . number_format($market_cap / 1000000, 0) . 'M';
                                    }
                                    ?>
                                </td>
                                <td class="chart-col">
                                    <?php
                                    // نمودار واقعی با sparkline
                                    $sparkline = isset($crypto['sparkline_in_7d']['price']) ? $crypto['sparkline_in_7d']['price'] : array();
                                    if (!empty($sparkline) && is_array($sparkline)) :
                                        // محاسبه min و max برای نرمال‌سازی
                                        $min_price = min($sparkline);
                                        $max_price = max($sparkline);
                                        $price_range = $max_price - $min_price;
                                        
                                        // ایجاد نقاط برای SVG
                                        $points = array();
                                        $width = 100;
                                        $height = 30;
                                        $step = $width / (count($sparkline) - 1);
                                        
                                        foreach ($sparkline as $i => $price) {
                                            $x = $i * $step;
                                            // نرمال‌سازی معکوس (چون در SVG y بالا کوچکتر است)
                                            $y = $price_range > 0 ? $height - (($price - $min_price) / $price_range * $height) : $height / 2;
                                            $points[] = "$x,$y";
                                        }
                                        $points_str = implode(' ', $points);
                                    ?>
                                    <div class="mini-chart" data-trend="<?php echo $change_24h >= 0 ? 'up' : 'down'; ?>">
                                        <svg width="100" height="30" viewBox="0 0 100 30">
                                            <polyline points="<?php echo esc_attr($points_str); ?>" 
                                                      fill="none" 
                                                      stroke="<?php echo $change_24h >= 0 ? '#16c784' : '#ea3943'; ?>" 
                                                      stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <?php else : ?>
                                    <div class="mini-chart" data-trend="<?php echo $change_24h >= 0 ? 'up' : 'down'; ?>">
                                        <span style="color: #94a3b8; font-size: 11px;">—</span>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <?php if ($show_buy_button) : ?>
                                <td class="buy-col" onclick="event.stopPropagation();">
                                    <a href="<?php echo esc_url($buy_link); ?>" 
                                       class="buy-crypto-btn" 
                                       target="_blank" 
                                       rel="noopener noreferrer">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2 2L3.5 2L3.9 4M14 10L4 10L3.5 2M6 14C6 14.5523 5.55228 15 5 15C4.44772 15 4 14.5523 4 14C4 13.4477 4.44772 13 5 13C5.55228 13 6 13.4477 6 14ZM13 14C13 14.5523 12.5523 15 12 15C11.4477 15 11 14.5523 11 14C11 13.4477 11.4477 13 12 13C12.5523 13 13 13.4477 13 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        خرید
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-footer">
                <a href="<?php echo home_url('/all-cryptocurrencies'); ?>" class="load-more-btn">
                   <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                       <path d="M10 4V16M10 16L6 12M10 16L14 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                   </svg>
                   مشاهده ادامه لیست
                </a>
            </div>
        </div>
    </section>

    <!-- بخش اخبار -->
    <?php
    $show_news = get_theme_mod('show_news_section', '1');
    if ($show_news == '1') :
    ?>
    <section class="cg-news-section-advanced tf-news" data-aos="fade-up">
        <div class="section-container container">
            <div class="section-header-advanced">
                <div class="section-title-group">
                    <h2 class="section-title-advanced">
                        <span class="title-icon">📰</span>
                        <span>آخرین اخبار</span>
                    </h2>
                    <p class="section-subtitle">تازه‌ترین رویدادهای دنیای کریپتو</p>
                </div>
                
                <a href="<?php echo home_url('/news'); ?>" class="btn-view-all-simple">
                    همه اخبار →
                </a>
            </div>
            
            <div class="news-grid-advanced">
                <?php
                $news_query = new WP_Query(array(
                    'posts_per_page' => 6,
                    'post_status' => 'publish'
                ));
                
                if ($news_query->have_posts()) :
                    while ($news_query->have_posts()) : $news_query->the_post();
                ?>
                    <article class="news-card-advanced">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="news-image-wrapper">
                                <img src="<?php the_post_thumbnail_url('large'); ?>" 
                                     alt="<?php the_title_attribute(); ?>" 
                                     class="news-image-advanced">
                                <div class="news-category">
                                    <?php 
                                    $categories = get_the_category();
                                    echo $categories ? esc_html($categories[0]->name) : 'اخبار';
                                    ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="news-image-wrapper">
                                <div class="news-image-placeholder" style="height: 220px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                                    📰
                                </div>
                                <div class="news-category">اخبار</div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="news-content-advanced">
                            <div class="news-meta">
                                <span class="news-date">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <path d="M7 13C10.3137 13 13 10.3137 13 7C13 3.68629 10.3137 1 7 1C3.68629 1 1 3.68629 1 7C1 10.3137 3.68629 13 7 13Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M7 3.5V7L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> پیش
                                </span>
                            </div>
                            
                            <h3 class="news-title-advanced">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            
                            <p class="news-excerpt-advanced">
                                <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                            </p>
                            
                            <a href="<?php the_permalink(); ?>" class="news-read-more">
                                <span>ادامه مطلب</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </a>
                        </div>
                    </article>
                <?php 
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <div class="empty-news-state" style="grid-column: 1 / -1; text-align: center; padding: 80px 40px;">
                        <div style="font-size: 64px; margin-bottom: 16px;">📝</div>
                        <h3 style="font-size: 24px; margin: 0 0 8px 0; color: #0f172a;">هنوز خبری منتشر نشده</h3>
                        <p style="color: #64748b; margin: 0 0 24px 0;">از بخش "نوشته‌ها" اخبار جدید اضافه کنید</p>
                        <a href="<?php echo admin_url('post-new.php'); ?>" class="btn-hero-primary">
                            <span>افزودن خبر جدید</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Articles & Education -->
    <section class="articles-section">
        <div class="container">
            <div class="section-header">
                <h2>
                    <i class="icon">📚</i>
                    <span>مقالات و آموزش</span>
                </h2>
                <a href="<?php echo home_url('/blog'); ?>" class="view-all-link">
                    همه مقالات
                    <i class="icon">←</i>
                </a>
            </div>
            
            <div class="articles-grid">
                <?php
                // دریافت مقالات - با جداسازی کامل از اخبار
                // ابتدا سعی می‌کنیم از meta field استفاده کنیم
                $articles = get_posts(array(
                    'posts_per_page' => 6,
                    'post_status' => 'publish',
                    'post_type' => 'post',
                    'meta_query' => array(
                        array(
                            'key' => 'post_type_meta',
                            'value' => 'article',
                            'compare' => '='
                        )
                    )
                ));
                
                // اگر مقاله‌ای از meta نیامد، از category استفاده کن
                if (empty($articles)) {
                    $articles = get_posts(array(
                        'posts_per_page' => 6,
                        'post_status' => 'publish',
                        'category_name' => 'article,articles,tutorial,guide,education,مقاله,آموزش',
                        'meta_query' => array(
                            array(
                                'key' => 'breaking_news',
                                'compare' => 'NOT EXISTS'
                            )
                        )
                    ));
                }
                
                // حذف پست‌هایی که breaking_news هستند
                if (!empty($articles)) {
                    $articles = array_filter($articles, function($post) {
                        $is_breaking = get_post_meta($post->ID, 'breaking_news', true);
                        return empty($is_breaking) || $is_breaking != '1';
                    });
                }
                
                if (!empty($articles)) :
                    foreach ($articles as $article) :
                        $categories = get_the_category($article->ID);
                        $cat_name = !empty($categories) ? $categories[0]->name : 'مقاله';
                ?>
                    <article class="article-card">
                        <?php if (has_post_thumbnail($article->ID)) : ?>
                            <div class="article-image">
                                <img src="<?php echo get_the_post_thumbnail_url($article->ID, 'medium'); ?>" alt="">
                                <div class="article-category"><?php echo esc_html($cat_name); ?></div>
                            </div>
                        <?php else : ?>
                            <div class="article-image placeholder">
                                <div class="placeholder-icon">📄</div>
                                <div class="article-category"><?php echo esc_html($cat_name); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="article-content">
                            <div class="article-meta">
                                <span class="date"><?php echo human_time_diff(get_the_time('U', $article->ID), current_time('timestamp')); ?> پیش</span>
                            </div>
                            <h3 class="article-title">
                                <a href="<?php echo get_permalink($article->ID); ?>">
                                    <?php echo esc_html($article->post_title); ?>
                                </a>
                            </h3>
                            <p class="article-excerpt">
                                <?php echo wp_trim_words($article->post_excerpt, 20); ?>
                            </p>
                            <a href="<?php echo get_permalink($article->ID); ?>" class="read-more">
                                ادامه مطلب ←
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php else : ?>
                    <div class="empty-state" style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                        <div style="font-size: 64px; margin-bottom: 16px;">📚</div>
                        <h3 style="font-size: 24px; margin: 0 0 8px 0;">هنوز مقاله‌ای منتشر نشده</h3>
                        <p style="color: #64748b;">از بخش "مقالات" در پنل ادمین، مقاله جدید اضافه کنید</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<style>
/* ===== Arzdigital Main Styles ===== */
:root {
    --primary: #0f766e;
    --primary-hover: #14b8a6;
    --success: #16c784;
    --danger: #ea3943;
    --text-primary: #0f172a;
    --text-secondary: #64748b;
    --text-tertiary: #94a3b8;
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --border-color: #e2e8f0;
}

.arzdigital-main {
    background: var(--bg-secondary);
    min-height: 100vh;
}

.container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Dashboard Section - New Design */
.dashboard-section {
    padding: 0 0 20px 0;
    background: var(--bg-secondary);
    margin-top: 10px;
}

.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    gap: 16px;
}

.card {
    background-color: #ffffff;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
}

.urgent-news-btn {
    padding: 5px 12px;
    border: none;
    border-radius: 5px;
    background-color: #ff9800;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.urgent-news-btn:hover {
    background-color: #f57c00;
}

/* بخش بالایی */
.top-section-grid {
    display: grid;
    grid-template-columns: 2.4fr 1fr;
    gap: 16px;
    align-items: stretch; /* کش آمدن آیتم‌ها برای هم‌ارتفاع شدن دو کارت */
}

/* اسلایدر اخبار - IMPROVED */
.featured-stories .card-body {
    display: grid;
    gap: 10px;
}

.featured-slider {
    position: relative;
    height: 380px;
}

.featured-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.6s ease;
}

.featured-slide.active {
    opacity: 1;
    position: relative;
}

.featured-slide .image-placeholder {
    width: 100%;
    height: 380px;
    background-color: #34495e;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
    color: #fff;
    overflow: hidden;
    position: relative;
}

/* Progress Bar */
.slide-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 0 0 8px 8px;
    overflow: hidden;
}

.slide-progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #ff9800 0%, #f57c00 100%);
    transition: width 0.1s linear;
}

.featured-slide.active .slide-progress-fill {
    animation: progressAnimation 5s linear forwards;
}

@keyframes progressAnimation {
    from { width: 0%; }
    to { width: 100%; }
}

.featured-news-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 132px; /* حدودا 3 عنوان قابل مشاهده */
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
    padding-right: 2px;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge legacy */
}

/* هنگام فعال بودن اسکرول خودکار، هرگونه انیمیشن قبلی غیرفعال شود */
.featured-news-list.auto-scroll {
    animation: none !important;
}

/* حالت تک ستونی */
.featured-news-list:not(.multi-column) {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* حالت چندستونی دیگر استفاده نمی‌شود؛ فهرست به‌صورت عمودی اسکرول می‌شود */
.featured-news-list.multi-column { display: block; }

/* حالت خاص برای 5 خبر - 3 تا یک طرف، 2 تا طرف دیگر */
.featured-news-list.multi-column[data-count="5"] {
    grid-template-columns: repeat(2, 1fr);
}

.featured-news-list .news-item {
    padding: 8px 10px;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: transparent;
    cursor: pointer;
}

.featured-news-list .news-item:hover {
    background: var(--bg-secondary);
}

.featured-news-list .news-item p {
    margin: 0;
    font-size: 0.85rem;
    line-height: 1.5;
    transition: all 0.3s ease;
}

/* عنوان فعال - بزرگتر و Bold */
.featured-news-list .news-item.active-news p {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--primary);
}

/* تنظیم اندازه فونت برای حالت چند ستونی */
.featured-news-list.multi-column .news-item p {
    font-size: 0.80rem;
}

.featured-news-list.multi-column .news-item.active-news p {
    font-size: 0.90rem;
}

/* تک ستونی - فونت بزرگتر */
.featured-news-list:not(.multi-column) .news-item p {
    font-size: 0.90rem;
}

.featured-news-list:not(.multi-column) .news-item.active-news p {
    font-size: 1rem;
}

.featured-news-list .news-item a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.3s;
}

.featured-news-list .news-item.active-news a {
    color: var(--primary);
}

.featured-news-list .news-item a:hover {
    color: var(--primary);
}

/* ارزهای ترند - آیکن‌های کوچک‌تر */
/* ارزهای ترند باید کل ارتفاع ستون را پر کنند */
.trend-values { 
    display: flex; 
    flex-direction: column; 
    height: 100%; /* پر کردن ارتفاع ردیف */
}

.trend-values .card-body {
    display: grid;
    grid-template-rows: repeat(6, auto);
    gap: 4px;
    flex: 1; /* بدنه کارت کش بیاید تا دات‌ها به پایین بچسبند */
}

.coin-item {
    display: grid;
    grid-template-columns: 2.6fr 2fr 1fr;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid var(--border-color);
}

.coin-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.coin-item:last-child {
    border-bottom: none;
}

.coin-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
}

.coin-logo-img {
    width: 24px !important; /* کوچک‌تر */
    height: 24px !important;
    border-radius: 50%;
    object-fit: cover;
}

.coin-name {
    font-size: 0.85rem;
}

.coin-price-change {
    text-align: right;
    font-size: 0.85rem;
}

.price {
    font-weight: 700;
    margin-bottom: 2px;
}

.price-fa {
    font-size: 0.7rem;
    color: #777;
}

.change {
    font-weight: 700;
    font-size: 0.75rem;
}

.change.down {
    color: #e74c3c;
}

.change.up {
    color: #2ecc71;
}

.mini-chart {
    height: 20px;
    border-radius: 4px;
}

.slider-dots {
    text-align: center;
    margin-top: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}

.slider-dots .dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    background-color: #cbd5e1;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.slider-dots .dot.active {
    background-color: var(--primary);
    width: 24px;
    border-radius: 12px;
}



.top-losers .item,
.top-gainers .item {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 10px;
    padding: 10px 8px;
    border-bottom: 1px solid var(--border-color);
    border-radius: 8px;
    transition: all 0.3s;
}

.clickable-item:hover {
    background: var(--bg-secondary);
    transform: translateX(-3px);
}

.top-losers .item:last-child,
.top-gainers .item:last-child {
    border-bottom: none;
}

.coin-icon-small {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.change-percent {
    font-weight: 700;
    font-size: 0.85rem;
    padding: 4px 10px;
    border-radius: 6px;
    white-space: nowrap;
}

.change-percent.down {
    color: #e74c3c;
    background-color: #fceaea;
}

.change-percent.up {
    color: #2ecc71;
    background-color: #e6f9ee;
}

.coin-name-symbol {
    flex-grow: 1;
    font-weight: 700;
    text-align: right;
    font-size: 0.9rem;
}

.coin-name-symbol .symbol {
    font-size: 0.75rem;
    color: #777;
    font-weight: 400;
    display: block;
    margin-top: 2px;
}

.rank-number {
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-secondary);
    padding: 4px 0;
}




.stat-value {
    font-weight: 800;
    font-size: 1.3rem;
    margin-bottom: 8px;
    color: var(--text-primary);
}

.stat-value.down {
    color: #e74c3c;
}

.stat-change {
    font-size: 0.8rem;
    font-weight: 700;
    margin-top: 4px;
}

.stat-change.down {
    color: #e74c3c;
}

.stat-change.up {
    color: #2ecc71;
}

/* شاخص ترس و طمع - ENHANCED */
.fear-greed-box {
    grid-row: span 2;
    min-height: 215px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.fear-greed-gauge {
    position: relative;
    width: 100%;
    height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.gauge-bars {
    display: flex;
    gap: 3px;
    justify-content: center;
    width: 150px;
    height: 80px;
    position: relative;
    transform: scaleY(-1);
    border-radius: 100px 100px 0 0;
    overflow: hidden;
}

.gauge-bar {
    flex: 1;
    height: 100%;
    border-radius: 8px 8px 0 0;
}

.gauge-bar.extreme-fear { background: #ea3943; }
.gauge-bar.fear { background: #f6465d; }
.gauge-bar.neutral { background: #fbbf24; }
.gauge-bar.greed { background: #10b981; }
.gauge-bar.extreme-greed { background: #16c784; }

.gauge-pointer {
    position: absolute;
    width: 2px;
    height: 60px;
    background: #0f172a;
    transform-origin: bottom center;
    top: 20px;
    transition: transform 0.5s ease;
}

.pointer-arrow {
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 13px solid #0f172a;
    position: absolute;
    top: -10px;
    left: -5px;
}

.gauge-value {
    font-size: 2rem;
    font-weight: 900;
    color: var(--text-primary);
    margin-top: 10px;
}

.gauge-label {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-secondary);
    margin-top: 4px;
}

/* نمودار mini برای ارزش بازار و تتر */
.mini-trend-chart {
    margin: 8px 0;
    opacity: 0.7;
}

.market-cap-box,
.tether-box {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.dominance-item {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 6px 0;
    font-size: 0.85rem;
    font-weight: 700;
}

/* جدول قیمت لحظه‌ای - FIXED */
.crypto-table-section {
    padding: 20px 0 30px 0;
}

.crypto-table-section .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.crypto-table-section h2 {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
}

.crypto-table-section .header-actions {
    display: flex;
    gap: 16px;
    align-items: center;
}

/* جدول بهبود یافته */
.crypto-table-wrapper {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.crypto-table {
    width: 100%;
    border-collapse: collapse;
}

.crypto-table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.crypto-table thead th {
    padding: 16px 12px;
    text-align: right;
    font-weight: 800;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

/* سرستون‌های قابل مرتب‌سازی */
.crypto-table thead th.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
    transition: all 0.3s ease;
}

.crypto-table thead th.sortable:hover {
    background: rgba(15, 118, 110, 0.05);
    color: var(--primary);
}

.crypto-table thead th.sortable::after {
    content: '⇅';
    margin-right: 6px;
    opacity: 0.3;
    font-size: 0.9rem;
}

.crypto-table thead th.sortable.sort-asc::after {
    content: '▲';
    opacity: 1;
    color: var(--primary);
}

.crypto-table thead th.sortable.sort-desc::after {
    content: '▼';
    opacity: 1;
    color: var(--primary);
}

.crypto-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.crypto-table tbody tr:hover {
    background: var(--bg-secondary);
    transform: scale(1.005);
}

.crypto-table tbody td {
    padding: 16px 12px;
    font-size: 0.95rem;
}

.rank-col {
    width: 60px;
    text-align: center;
}

/* دکمه خرید */
.buy-col {
    text-align: center;
    width: 110px;
    padding: 12px !important;
}

.buy-crypto-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 18px;
    width: 100%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(15, 118, 110, 0.2);
}

.buy-crypto-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
    background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary) 100%);
    color: white;
}

.buy-crypto-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.price-irr {
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    color: var(--text-primary);
}

.price-irr .currency-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-right: 3px;
}

/* دکمه مشاهده ادامه لیست */
.table-footer {
    margin-top: 24px;
    text-align: center;
}

.load-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 14px 32px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
    color: white;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.2);
}

.load-more-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(15, 118, 110, 0.3);
    color: white;
}

.load-more-btn svg {
    transition: transform 0.3s ease;
}

.load-more-btn:hover svg {
    transform: translateY(3px);
}

/* تب‌ها - فعال‌سازی */
.view-tabs {
    display: flex;
    gap: 8px;
}

.tab-btn {
    padding: 8px 16px;
    border: 1px solid var(--border-color);
    background: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.tab-btn:hover {
    background: var(--bg-secondary);
}

.tab-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

@media (max-width: 1024px) {
    .top-section-grid {
        grid-template-columns: 1fr;
    }
    
    .bottom-section-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .stats-grid-container {
        grid-column: 1 / -1;
    }
    
    .buy-crypto-btn {
        padding: 6px 12px;
        font-size: 0.75rem;
    }
}

@media (max-width: 768px) {
    .bottom-section-grid {
        grid-template-columns: 1fr;
    }
    
    .featured-news-list.multi-column {
        grid-template-columns: 1fr;
    }
    
    .crypto-table thead th,
    .crypto-table tbody td {
        padding: 10px 8px;
        font-size: 0.8rem;
    }
    
    .buy-col {
        display: none;
    }
}
</style>

<script>
// تب‌های فیلتر جدول ارزها
document.addEventListener('DOMContentLoaded', function() {
    if (window.__featuredSliderInitialized) {
        return;
    }
    window.__featuredSliderInitialized = true;
    const tabs = document.querySelectorAll('.tab-btn');
    const tableRows = document.querySelectorAll('.crypto-table tbody tr');
    
    // دیفای کوین‌ها (لیست نمونه - می‌تونید گسترش بدید)
    const defiCoins = ['uniswap', 'aave', 'compound', 'maker', 'curve-dao-token', 'sushi', 'pancakeswap-token', 'synthetix-network-token'];
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // حذف active از همه تب‌ها
            tabs.forEach(t => t.classList.remove('active'));
            // اضافه کردن active به تب کلیک شده
            this.classList.add('active');
            
            const selectedTab = this.getAttribute('data-tab');
            
            tableRows.forEach(row => {
                const cryptoName = row.querySelector('.name-info .name');
                if (!cryptoName) return;
                
                const coinName = cryptoName.textContent.trim().toLowerCase();
                
                if (selectedTab === 'all') {
                    row.style.display = '';
                } else if (selectedTab === 'favorites') {
                    // فعلا همه رو نشون بده - می‌تونید سیستم favorite اضافه کنید
                    row.style.display = '';
                } else if (selectedTab === 'defi') {
                    // فقط دیفای کوین‌ها نشون بده
                    const isDefi = defiCoins.some(defi => coinName.includes(defi.replace(/-/g, ' ')));
                    row.style.display = isDefi ? '' : 'none';
                }
            });
        });
    });
    
    // مرتب‌سازی جدول
    const sortableHeaders = document.querySelectorAll('.crypto-table thead th.sortable');
    let currentSortColumn = null;
    let currentSortDirection = 'desc';
    
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortBy = this.getAttribute('data-sort');
            
            // تغییر جهت مرتب‌سازی
            if (currentSortColumn === sortBy) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortDirection = 'desc';
            }
            currentSortColumn = sortBy;
            
            // حذف کلاس‌های sort از همه header ها
            sortableHeaders.forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            
            // اضافه کردن کلاس sort به header فعلی
            this.classList.add(currentSortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
            
            // مرتب‌سازی ردیف‌ها
            const tbody = document.querySelector('.crypto-table tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                let aValue, bValue;
                
                if (sortBy === 'rank') {
                    aValue = parseInt(a.getAttribute('data-rank'));
                    bValue = parseInt(b.getAttribute('data-rank'));
                } else if (sortBy === 'name') {
                    aValue = a.querySelector('.name-info .name').textContent.trim();
                    bValue = b.querySelector('.name-info .name').textContent.trim();
                    return currentSortDirection === 'asc' 
                        ? aValue.localeCompare(bValue)
                        : bValue.localeCompare(aValue);
                } else if (sortBy === 'price' || sortBy === 'price_usd') {
                    aValue = parseFloat(a.getAttribute('data-price'));
                    bValue = parseFloat(b.getAttribute('data-price'));
                } else if (sortBy === 'change_24h') {
                    aValue = parseFloat(a.getAttribute('data-change-24h'));
                    bValue = parseFloat(b.getAttribute('data-change-24h'));
                } else if (sortBy === 'change_7d') {
                    aValue = parseFloat(a.getAttribute('data-change-7d'));
                    bValue = parseFloat(b.getAttribute('data-change-7d'));
                } else if (sortBy === 'change_30d') {
                    aValue = parseFloat(a.getAttribute('data-change-30d'));
                    bValue = parseFloat(b.getAttribute('data-change-30d'));
                } else if (sortBy === 'volume') {
                    aValue = parseFloat(a.getAttribute('data-volume'));
                    bValue = parseFloat(b.getAttribute('data-volume'));
                } else if (sortBy === 'marketcap') {
                    aValue = parseFloat(a.getAttribute('data-marketcap'));
                    bValue = parseFloat(b.getAttribute('data-marketcap'));
                }
                
                return currentSortDirection === 'asc' ? aValue - bValue : bValue - aValue;
            });
            
            // اضافه کردن ردیف‌های مرتب شده به جدول
            rows.forEach(row => tbody.appendChild(row));
        });
    });
    
    // اسکریپت اسلایدر اخبار به فایل جداگانه منتقل شده است (assets/js/news-slider.js)
});
</script>

<?php get_footer(); ?>
