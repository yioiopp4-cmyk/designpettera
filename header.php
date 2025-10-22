<?php
/**
 * Header Arzdigital Style - مشابه سایت arzdigital.com
 *
 * @package CryptoSekhyab
 */

// دریافت داده‌های بازار
$api_source = get_option('crypto_api_source', 'coinmarketcap');
$usdt_rate = get_option('crypto_sekhyab_usdt_price', 114850);

$global_data = array(
    'active_cryptocurrencies' => 10000,
    'total_market_cap' => array('usd' => 0),
    'market_cap_percentage' => array('btc' => 0),
);

try {
    if ($api_source == 'coinmarketcap') {
        $cmc = cmc_api();
        $global_data_raw = $cmc->get_global_metrics();
        if (is_array($global_data_raw)) {
            $global_data = array(
                'active_cryptocurrencies' => isset($global_data_raw['active_cryptocurrencies']) ? $global_data_raw['active_cryptocurrencies'] : 10000,
                'total_market_cap' => array('usd' => isset($global_data_raw['quote']['USD']['total_market_cap']) ? $global_data_raw['quote']['USD']['total_market_cap'] : 0),
                'market_cap_percentage' => array('btc' => isset($global_data_raw['btc_dominance']) ? $global_data_raw['btc_dominance'] : 0),
            );
        }
    } else {
        $api = cg_api();
        $global_data = $api->get_global_market_data();
        if (!is_array($global_data)) {
            $global_data = array(
                'active_cryptocurrencies' => 10000,
                'total_market_cap' => array('usd' => 0),
                'market_cap_percentage' => array('btc' => 0),
            );
        }
    }
} catch (Exception $e) {
    // در صورت خطا، داده‌های پیش‌فرض
}

$total_market_cap = isset($global_data['total_market_cap']['usd']) ? $global_data['total_market_cap']['usd'] : 0;
$btc_dominance = isset($global_data['market_cap_percentage']['btc']) ? $global_data['market_cap_percentage']['btc'] : 0;
$active_cryptos = isset($global_data['active_cryptocurrencies']) ? $global_data['active_cryptocurrencies'] : 10000;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class('arzdigital-style'); ?>>

<?php wp_body_open(); ?>

<!-- Inline overrides removed; using consolidated styles and dynamic offset -->

<header class="arzdigital-header">
    
    
    <!-- Main Header -->
    <div class="arzdigital-main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?php echo home_url(); ?>" class="header-logo">
                    <div class="logo-icon">₿</div>
                    <span class="logo-text"><?php bloginfo('name'); ?></span>
                </a>
                
                <div class="header-divider"></div>
                
                <!-- Navigation -->
                <nav class="header-nav">
                    <div class="quick-access-menu">
                        <button class="quick-access-btn">
                            <i class="icon">⚡</i>
                            <span>دسترسی سریع</span>
                        </button>
                        <div class="quick-access-dropdown">
                            <div class="quick-access-grid">
                                <?php
                                $quick_access_items = get_option('crypto_sekhyab_quick_access_items', array(
                                    array('icon' => '₿', 'title' => 'بیت کوین', 'link' => '#'),
                                    array('icon' => '₮', 'title' => 'تتر', 'link' => '#'),
                                    array('icon' => 'Ξ', 'title' => 'اتریوم', 'link' => '#'),
                                    array('icon' => '📋', 'title' => 'لیست ارزها', 'link' => home_url('/all-cryptocurrencies')),
                                    array('icon' => '🔄', 'title' => 'تبدیل ارز', 'link' => '#'),
                                    array('icon' => '📈', 'title' => 'نمودار سود', 'link' => '#'),
                                ));
                                
                                foreach ($quick_access_items as $item) :
                                ?>
                                <a href="<?php echo esc_url($item['link']); ?>" class="quick-access-item">
                                    <div class="item-icon"><?php echo esc_html($item['icon']); ?></div>
                                    <span><?php echo esc_html($item['title']); ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'main-menu',
                        'fallback_cb' => false
                    ));
                    ?>
                    
                    <!-- Search -->
                    <div class="header-search">
                        <input type="search" placeholder="جستجوی ارز..." class="search-input">
                        <i class="search-icon">🔍</i>
                    </div>
                </nav>
                
                <!-- Auth Buttons -->
                <div class="header-auth">
                                    <div class="toolbar-actions">
                    <a href="#" class="toolbar-link">
                        <i class="icon">⭐</i>
                        <span>تحت‌نظر</span>
                    </a>
                    <a href="#" class="toolbar-link">
                        <i class="icon">📊</i>
                        <span>پورتفولیو</span>
                    </a>
                    <a href="#" class="toolbar-link">
                        <i class="icon">🛒</i>
                        <span>خرید</span>
                    </a>
                    <div class="toolbar-divider"></div>
                    <button class="toolbar-icon-btn" title="دانلود اپلیکیشن">
                        <i class="icon">📱</i>
                    </button>
                </div>
                    <button class="btn-register">
                        <i class="icon">👤</i>
                        <span>ثبت‌نام</span>
                    </button>
                    <button class="btn-login">ورود</button>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>
</header>

<style>
/* ===== Arzdigital Header Styles ===== */
.arzdigital-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Top Toolbar */
.arzdigital-top-toolbar {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    min-height: 44px;
    padding: 6px 0;
}

.top-toolbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
    row-gap: 6px;
}

.toolbar-stats {
    display: flex;
    gap: 24px;
    align-items: center;
}

.stat-item {
    display: flex;
    gap: 6px;
    align-items: center;
    font-size: 12px;
}

.stat-label {
    color: #64748b;
    font-weight: 500;
}

.stat-value {
    color: #0f766e;
    font-weight: 700;
}

.tether-price {
    animation: pulse-subtle 2s ease-in-out infinite;
}

@keyframes pulse-subtle {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.85; }
}

.toolbar-actions {
    display: flex;
    align-items: center;
    gap: 16px;
}

.toolbar-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #64748b;
    text-decoration: none;
    transition: color 0.3s ease;
}

.toolbar-link:hover {
    color: #0f766e;
}

.toolbar-link .icon {
    font-size: 16px;
}

.toolbar-divider {
    width: 1px;
    height: 24px;
    background: #e2e8f0;
}

.toolbar-icon-btn {
    background: none;
    border: none;
    color: #64748b;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    transition: color 0.3s ease;
}

.toolbar-icon-btn:hover {
    color: #0f766e;
}

/* Main Header */
.arzdigital-main-header {
    background: #ffffff;
    position: relative;
    min-height: 64px;
    padding: 8px 0;
}

.header-content {
    display: grid;
    grid-template-columns: auto 1fr auto auto;
    grid-template-rows: auto auto;
    grid-template-areas:
        "logo divider auth toggle"
        "nav nav nav nav";
    align-items: center;
    gap: 16px 20px;
}

.header-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #0f172a;
}

/* Grid placement to avoid overlapping and force menu on new row */
.header-logo { grid-area: logo; }
.header-divider { grid-area: divider; }
.header-nav { grid-area: nav; width: 100%; }
.header-auth { grid-area: auth; justify-self: end; }
.mobile-menu-toggle { grid-area: toggle; justify-self: end; }

.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 24px;
    font-weight: 900;
}

.logo-text {
    font-size: 20px;
    font-weight: 900;
    color: #0f172a;
}

.header-divider {
    width: 1px;
    height: 40px;
    background: #e2e8f0;
}

.header-nav {
    display: flex;
    align-items: center;
    gap: 24px;
}

/* Quick Access Menu */
.quick-access-menu {
    position: relative;
}

.quick-access-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.quick-access-btn:hover {
    background: #f8fafc;
    color: #0f766e;
}

.quick-access-dropdown {
    position: absolute;
    top: calc(100% + 16px);
    right: 0;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    padding: 16px;
    min-width: 380px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 100;
}

.quick-access-menu:hover .quick-access-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.quick-access-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.quick-access-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 16px;
    border-radius: 12px;
    background: #f8fafc;
    text-decoration: none;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.quick-access-item:hover {
    background: #f1f5f9;
    color: #0f766e;
    transform: translateY(-2px);
}

.quick-access-item .item-icon {
    width: 48px;
    height: 48px;
    background: #ffffff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 900;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Main Menu */
.main-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

/* Submenu styling: show below the main menu, not overlaid */
.main-menu > li { position: relative; }
.main-menu li .sub-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0; /* RTL alignment */
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    padding: 8px 0;
    min-width: 220px;
    display: none;
    z-index: 1000;
}
.main-menu > li:hover > .sub-menu { display: block; }
.main-menu li .sub-menu li a {
    padding: 10px 16px;
    display: block;
    white-space: nowrap;
}
/* Nested submenu (flyout) */
.main-menu li .sub-menu li { position: relative; }
.main-menu li .sub-menu li .sub-menu {
    top: 0;
    right: 100%;
    margin-right: 8px;
}

.main-menu li a {
    display: block;
    padding: 8px 16px;
    color: #64748b;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.main-menu li a:hover {
    background: #f8fafc;
    color: #0f766e;
}

/* Search */
.header-search {
    position: relative;
    flex: 1;
    max-width: 320px;
}

.search-input {
    width: 100%;
    padding: 10px 16px 10px 44px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #0f766e;
    box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    color: #94a3b8;
}

/* Auth Buttons */
.header-auth {
    display: flex;
    gap: 12px;
}

.btn-register {
    background: #0f766e;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-register:hover {
    background: #14b8a6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
}

.btn-login {
    background: #f1f5f9;
    color: #0f172a;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-login:hover {
    background: #e2e8f0;
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
}

.mobile-menu-toggle span {
    width: 24px;
    height: 3px;
    background: #0f172a;
    border-radius: 2px;
    transition: all 0.3s ease;
}

/* Responsive */
@media (max-width: 1024px) {
    .header-nav { display: none; }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    .toolbar-stats .stat-item:nth-child(n+3) {
        display: none;
    }

    /* Mobile menu panel below the header bar (in normal flow) */
    .header-nav.active {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        padding: 12px 16px;
        gap: 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .header-nav.active .main-menu {
        flex-direction: column;
        gap: 2px;
    }
    .header-nav.active .main-menu li .sub-menu {
        position: static;
        display: none;
        box-shadow: none;
        border: none;
        padding: 0;
        margin: 0;
    }
    .header-nav.active .main-menu li.open > .sub-menu,
    .header-nav.active .main-menu li:hover > .sub-menu {
        display: block;
    }
}

@media (max-width: 768px) {
    .arzdigital-top-toolbar {
        display: none;
    }
    
    .header-auth {
        display: none;
    }
}

/* CRITICAL: Adjust body padding for fixed header (dynamic via CSS var) */
body.arzdigital-style {
    /* Fallback value; real value is set by JS based on header height */
    --arzdigital-header-offset: 80px;
    padding-top: var(--arzdigital-header-offset) !important;
    margin-top: 0 !important;
}

body.arzdigital-style .cg-main,
body.arzdigital-style .arzdigital-main {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* Force header to be fixed and at top */
body.arzdigital-style .arzdigital-header {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 999999 !important;
    background: #fff !important;
}

body.arzdigital-style .arzdigital-top-toolbar {
    position: relative !important;
    z-index: 1000 !important;
}

body.arzdigital-style .arzdigital-main-header {
    position: relative !important;
    z-index: 999 !important;
}

@media (max-width: 768px) {
    /* Padding remains dynamic; no hardcoded values on mobile */
}
</style>

<script>
jQuery(document).ready(function($) {
    // Mobile menu toggle
    $('.mobile-menu-toggle').on('click', function() {
        $(this).toggleClass('active');
        $('.header-nav').toggleClass('active');
        // Recalculate after menu expands/collapses
        setTimeout(updateHeaderOffset, 0);
    });
    
    // Search functionality
    $('.search-input').on('keypress', function(e) {
        if (e.which === 13) {
            const query = $(this).val();
            if (query) {
                window.location.href = '<?php echo home_url(); ?>/?s=' + encodeURIComponent(query);
            }
        }
    });

    // Dynamic header offset to prevent overlap with fixed header
    function updateHeaderOffset() {
        var $header = $('.arzdigital-header');
        if ($header.length === 0) return;
        // Measure top toolbar and main header individually to be robust
        var $toolbar = $header.find('.arzdigital-top-toolbar');
        var $main = $header.find('.arzdigital-main-header');
        var toolbarH = $toolbar.length ? Math.ceil($toolbar.outerHeight(true)) : 0;
        var mainH = $main.length ? Math.ceil($main.outerHeight(true)) : 0;
        var computed = toolbarH + mainH;
        // Fallback to full header height if zero
        if (!Number.isFinite(computed) || computed <= 0) {
            computed = Math.ceil($header.outerHeight(true)) || 108;
        }
        document.body.style.setProperty('--arzdigital-header-offset', computed + 'px');
    }

    // Initial calculation and responsive updates
    updateHeaderOffset();
    // Recalculate after a short delay for fonts/icons/layout
    setTimeout(updateHeaderOffset, 100);
    setTimeout(updateHeaderOffset, 300);
    setTimeout(updateHeaderOffset, 800);

    // Window load and resize
    $(window).on('load', updateHeaderOffset);
    var resizeTimeout;
    $(window).on('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateHeaderOffset, 120);
    });

    // Observe header size changes
    try {
        if (window.ResizeObserver) {
            var ro = new ResizeObserver(function() { updateHeaderOffset(); });
            var headerEl = document.querySelector('.arzdigital-header');
            if (headerEl) {
                ro.observe(headerEl);
                var toolbarEl = headerEl.querySelector('.arzdigital-top-toolbar');
                var mainEl = headerEl.querySelector('.arzdigital-main-header');
                if (toolbarEl) ro.observe(toolbarEl);
                if (mainEl) ro.observe(mainEl);
            }
        }
    } catch (e) {}

    // Observe DOM mutations that can affect header height
    try {
            var headerNode = document.querySelector('.arzdigital-header');
            if (headerNode && window.MutationObserver) {
                var mo = new MutationObserver(function() { updateHeaderOffset(); });
                mo.observe(headerNode, { childList: true, subtree: true, attributes: true });
            }
    } catch (e) {}

    // Fonts readiness (affects text metrics)
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function() { updateHeaderOffset(); });
    }
});
</script>
