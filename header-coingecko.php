<?php
/**
 * Header CoinGecko Style
 *
 * @package CryptoSekhyab
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="cg-header">
    <div class="cg-header-content container">
        <a href="<?php echo home_url(); ?>" class="cg-logo">
            <div class="cg-logo-icon">₿</div>
            <span class="cg-logo-text"><?php bloginfo('name'); ?></span>
        </a>
        
        <nav class="cg-nav">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_class' => 'cg-nav-menu',
                'fallback_cb' => 'crypto_sekhyaby_fallback_menu'
            ));
            ?>
            
            <div class="cg-search-box">
                <span class="cg-search-icon">🔍</span>
                <input type="search" 
                       class="cg-search-input" 
                       placeholder="جستجوی ارز..." 
                       id="cg-search">
            </div>
        </nav>
        
        <button class="cg-mobile-menu-toggle" id="mobile-menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<style>
.cg-mobile-menu-toggle {
    display: none;
    flex-direction: column;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
}

.cg-mobile-menu-toggle span {
    width: 24px;
    height: 3px;
    background: var(--cg-text-primary);
    border-radius: 2px;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .cg-nav {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        flex-direction: column;
        gap: 16px;
    }
    
    .cg-nav.active {
        display: flex;
    }
    
    .cg-nav-menu {
        flex-direction: column;
        width: 100%;
    }
    
    .cg-search-input {
        width: 100%;
    }
    
    .cg-mobile-menu-toggle {
        display: flex;
    }
    
    .cg-mobile-menu-toggle.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }
    
    .cg-mobile-menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .cg-mobile-menu-toggle.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const nav = document.querySelector('.cg-nav');
    
    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            nav.classList.toggle('active');
        });
    }
    
    // Header scroll effect
    let lastScroll = 0;
    const header = document.querySelector('.cg-header');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });
    
    // Search functionality (basic)
    const searchInput = document.getElementById('cg-search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value;
                if (query) {
                    window.location.href = '<?php echo home_url(); ?>/?s=' + encodeURIComponent(query);
                }
            }
        });
    }
});
</script>
