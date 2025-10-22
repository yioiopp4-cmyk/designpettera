/**
 * کریپتو سخیاب - JavaScript اصلی
 * 
 * @package CryptoSekhyab
 */

(function($) {
    'use strict';

    // منوی موبایل
    function initMobileMenu() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const navigation = document.getElementById('site-navigation');

        if (menuToggle && navigation) {
            menuToggle.addEventListener('click', function() {
                navigation.classList.toggle('active');
            });
        }
    }

    // دریافت قیمت ارزها
    function fetchCryptoPrices() {
        const priceBox = document.getElementById('crypto-price-box');
        
        if (!priceBox) return;

        const coingeckoId = priceBox.getAttribute('data-coingecko-id');
        
        if (!coingeckoId) return;

        // ارسال درخواست AJAX
        $.ajax({
            url: cryptoSekhyabData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_crypto_prices',
                nonce: cryptoSekhyabData.nonce,
                crypto_ids: coingeckoId
            },
            success: function(response) {
                if (response.success && response.data[coingeckoId]) {
                    const data = response.data[coingeckoId];
                    const usdToIrr = parseFloat(cryptoSekhyabData.usdToIrr);
                    
                    // قیمت دلاری
                    const priceUsd = document.getElementById('price-usd');
                    if (priceUsd) {
                        priceUsd.innerHTML = '$' + formatNumber(data.usd);
                    }
                    
                    // قیمت تومانی
                    const priceIrr = document.getElementById('price-irr');
                    if (priceIrr) {
                        const irrPrice = data.usd * usdToIrr;
                        priceIrr.innerHTML = formatNumber(irrPrice) + ' تومان';
                    }
                    
                    // تغییرات 24 ساعته
                    const priceChange = document.getElementById('price-change');
                    if (priceChange && data.usd_24h_change) {
                        const change = data.usd_24h_change.toFixed(2);
                        const changeClass = change >= 0 ? 'positive' : 'negative';
                        const changeSymbol = change >= 0 ? '▲' : '▼';
                        priceChange.innerHTML = `<span class="price-change ${changeClass}">${changeSymbol} ${Math.abs(change)}%</span>`;
                    }
                    
                    // حجم بازار
                    const marketCap = document.getElementById('market-cap');
                    if (marketCap && data.usd_market_cap) {
                        marketCap.innerHTML = '$' + formatLargeNumber(data.usd_market_cap);
                    }
                }
            },
            error: function() {
                console.error('خطا در دریافت قیمت ارزها');
                const priceUsd = document.getElementById('price-usd');
                const priceIrr = document.getElementById('price-irr');
                const change = document.getElementById('price-change');
                const cap = document.getElementById('market-cap');
                if (priceUsd) priceUsd.textContent = '-';
                if (priceIrr) priceIrr.textContent = '-';
                if (change) change.textContent = '-';
                if (cap) cap.textContent = '-';
            }
        });
    }

    // فرمت کردن اعداد
    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // فرمت کردن اعداد بزرگ
    function formatLargeNumber(num) {
        if (num >= 1000000000000) {
            return (num / 1000000000000).toFixed(2) + 'T';
        } else if (num >= 1000000000) {
            return (num / 1000000000).toFixed(2) + 'B';
        } else if (num >= 1000000) {
            return (num / 1000000).toFixed(2) + 'M';
        }
        return formatNumber(num);
    }

    // جدول لیست ارزها
    function initCryptoTable() {
        const table = document.querySelector('.crypto-table');
        
        if (!table) return;

        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            row.addEventListener('click', function() {
                const link = this.getAttribute('data-link');
                if (link) {
                    window.location.href = link;
                }
            });
        });
    }

    // تغییر واحد پول (دلار/تومان)
    function initCurrencyToggle() {
        const toggleButtons = document.querySelectorAll('.currency-btn');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // حذف کلاس active از همه دکمه‌ها
                toggleButtons.forEach(btn => btn.classList.remove('active'));
                
                // اضافه کردن کلاس active به دکمه کلیک شده
                this.classList.add('active');
                
                const currency = this.getAttribute('data-currency');
                toggleCryptoPrices(currency);
            });
        });
    }

    // تغییر نمایش قیمت‌ها
    function toggleCryptoPrices(currency) {
        const priceElements = document.querySelectorAll('.crypto-price');
        const usdToIrr = parseFloat(cryptoSekhyabData.usdToIrr);
        
        priceElements.forEach(element => {
            const usdPrice = parseFloat(element.getAttribute('data-usd-price'));
            
            if (currency === 'usd') {
                element.textContent = '$' + formatNumber(usdPrice);
            } else {
                const irrPrice = usdPrice * usdToIrr;
                element.textContent = formatNumber(irrPrice) + ' تومان';
            }
        });
    }

    // انیمیشن عناصر هنگام اسکرول
    function initScrollAnimations() {
        const elements = document.querySelectorAll('.fade-in');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, {
            threshold: 0.1
        });
        
        elements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(element);
        });
    }

    // ب    // Price Ticker - تیکر قیمت
    function initPriceTicker() {
        const tickerContainer = document.getElementById('ticker-items');
        if (!tickerContainer) return;
        
        // دریافت قیمت ارزهای برتر برای تیکر
        $.ajax({
            url: cryptoSekhyabData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_ticker_cryptos',
                nonce: cryptoSekhyabData.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderTicker(response.data);
                }
            },
            error: function() {
                tickerContainer.innerHTML = '<div class="ticker-loader">خطا در بارگذاری</div>';
            }
        });
    }
    
    function renderTicker(cryptos) {
        const tickerContainer = document.getElementById('ticker-items');
        if (!tickerContainer || !cryptos.length) return;
        
        let tickerHTML = '';
        
        // تکرار آیتم‌ها برای اسکرول بی‌نهایت
        for (let i = 0; i < 2; i++) {
            cryptos.forEach(crypto => {
                const change = crypto.price_change_percentage_24h || 0;
                const changeClass = change >= 0 ? 'positive' : 'negative';
                const changeSymbol = change >= 0 ? '▲' : '▼';
                
                tickerHTML += `
                    <div class="ticker-item">
                        <img src="${crypto.image}" alt="${crypto.name}" class="ticker-logo">
                        <span class="ticker-name">${crypto.symbol.toUpperCase()}</span>
                        <span class="ticker-price">$${formatPrice(crypto.current_price)}</span>
                        <span class="ticker-change ${changeClass}">${changeSymbol} ${Math.abs(change).toFixed(2)}%</span>
                    </div>
                `;
            });
        }
        
        tickerContainer.innerHTML = tickerHTML;
    }
    
    function formatPrice(price) {
        if (price >= 1) {
            return price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        } else {
            return price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 6 });
        }
    }
    
    // Header Scroll Effect
    function initHeaderScroll() {
        const header = document.querySelector('.site-header');
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }
    
    // Mobile Menu Toggle Enhanced
    function initMobileMenuEnhanced() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const navigation = document.getElementById('site-navigation');
        
        if (menuToggle && navigation) {
            menuToggle.addEventListener('click', function() {
                navigation.classList.toggle('active');
                menuToggle.classList.toggle('active');
                document.body.classList.toggle('menu-open');
            });
            
            // بستن منو با کلیک خارج از آن
            document.addEventListener('click', function(e) {
                if (!menuToggle.contains(e.target) && !navigation.contains(e.target)) {
                    navigation.classList.remove('active');
                    menuToggle.classList.remove('active');
                    document.body.classList.remove('menu-open');
                }
            });
        }
    }
    
    // Back to Top Enhanced
    function initBackToTop() {
        const backToTop = document.getElementById('back-to-top');
        
        if (!backToTop) return;
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Smooth Scroll for Links
    function initSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    // Intersection Observer for Animations
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    entry.target.classList.add('animate-fadeInUp');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // اضافه کردن انیمیشن به المان‌های خاص
        const animateElements = document.querySelectorAll('.crypto-table-modern .table-row, .news-card, .trending-card, .stat-card');
        
        animateElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(12px)';
            el.style.transition = 'opacity .4s ease, transform .4s ease';
            el.style.animationDelay = `${index * 0.05}s`;
            observer.observe(el);
        });
    }
    
    // Crypto Table Click Handler
    function initCryptoTableClick() {
        const cryptoRows = document.querySelectorAll('.crypto-item');
        
        cryptoRows.forEach(row => {
            row.style.cursor = 'pointer';
            
            row.addEventListener('click', function(e) {
                // اگر روی لینکی کلیک شد، اجازه عمل پیش‌فرض
                if (e.target.tagName === 'A') return;
                
                const cryptoId = this.getAttribute('data-id');
                if (cryptoId) {
                    // می‌توانید به صفحه جزئیات ارز هدایت کنید
                    console.log('Clicked crypto:', cryptoId);
                }
            });
        });
    }

    // اجرا هنگام آماده شدن صفحه
    $(document).ready(function() {
        initMobileMenu();
        initMobileMenuEnhanced();
        initCryptoTable();
        initCryptoTableClick();
        initCurrencyToggle();
        initScrollAnimations();
        initHeaderScroll();
        initBackToTop();
        initSmoothScroll();
        initPriceTicker();
        
        // دریافت قیمت‌ها در صفحه ارز
        if (document.getElementById('crypto-price-box')) {
            fetchCryptoPrices();
            setInterval(fetchCryptoPrices, 15000);
        }
        
        // تب‌های صفحه ارز تکی
        const tabButtons = document.querySelectorAll('.tabs-nav .tab-btn');
        const tabContents = document.querySelectorAll('[data-tab-content]');
        if (tabButtons.length && tabContents.length) {
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    tabButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const tab = this.getAttribute('data-tab');
                    tabContents.forEach(c => {
                        const key = c.getAttribute('data-tab-content');
                        c.style.display = (key === tab || (tab === 'overview' && (key === 'overview'))) ? '' : 'none';
                    });
                });
            });
        }

        // تاریخچه قیمت - بارگذاری جدول
        $(document).on('click', '.tab-btn[data-tab="history"]', function() {
            const body = document.getElementById('history-body');
            if (!body) return;
            body.innerHTML = '<tr><td colspan="3">در حال بارگذاری...</td></tr>';
            fetchHistory('30');
        });

        $(document).on('click', '.history-range', function() {
            $('.history-range').removeClass('active');
            $(this).addClass('active');
            const days = $(this).data('days') || '30';
            fetchHistory(days);
        });

        function fetchHistory(days) {
            const coinId = (window.coingeckoId) || document.querySelector('[data-coingecko-id]')?.getAttribute('data-coingecko-id');
            const body = document.getElementById('history-body');
            if (!coinId || !body) return;
            $.ajax({
                url: (window.cryptoSekhyabData && cryptoSekhyabData.ajaxUrl) || 'about:blank',
                type: (window.cryptoSekhyabData ? 'POST' : 'GET'),
                data: window.cryptoSekhyabData ? {
                    action: 'get_market_chart',
                    nonce: cryptoSekhyabData.nonce,
                    coin_id: coinId,
                    days: days
                } : {},
                timeout: 15000,
                success: function(resp) {
                    const data = (resp && resp.success && resp.data) ? resp.data : resp;
                    if (!data || !data.prices) { body.innerHTML = '<tr><td colspan="3">داده‌ای یافت نشد</td></tr>'; return; }
                    const usdt = parseFloat(window.usdtRate || 0);
                    let rows = '';
                    const step = Math.max(1, Math.floor(data.prices.length / 60));
                    for (let i = data.prices.length - 1; i >= 0; i -= step) {
                        const [ts, usd] = data.prices[i];
                        const d = new Date(ts);
                        const dateStr = d.getFullYear() + '/' + String(d.getMonth()+1).padStart(2,'0') + '/' + String(d.getDate()).padStart(2,'0');
                        const irr = usd * (usdt || 0);
                        rows += '<tr>'+
                            '<td>'+dateStr+'</td>'+
                            '<td>$'+formatPrice(usd)+'</td>'+
                            '<td>'+Number(irr||0).toLocaleString('fa-IR')+' تومان</td>'+
                            '</tr>';
                        if (rows.length > 20000) break;
                    }
                    body.innerHTML = rows || '<tr><td colspan="3">—</td></tr>';
                },
                error: function(){ body.innerHTML = '<tr><td colspan="3">خطا در دریافت</td></tr>'; }
            });
        }

        // بروزرسانی تیکر هر 30 ثانیه
        setInterval(initPriceTicker, 30000);
    });

    // نمایش/پنهان کردن دکمه بازگشت به بالا (برای سازگاری با کد قدیمی)
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').addClass('show');
        } else {
            $('.back-to-top').removeClass('show');
        }
    });

    // کلیک روی دکمه بازگشت به بالا
    $('.back-to-top').click(function() {
        $('html, body').animate({scrollTop: 0}, 600);
        return false;
    });

})(jQuery);
