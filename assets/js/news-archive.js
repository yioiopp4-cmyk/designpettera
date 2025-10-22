/**
 * JavaScript صفحه اخبار - تعاملات پیشرفته
 */

jQuery(document).ready(function($) {
    
    // ===== تغییر تم (روشن/تیره) =====
    const themeToggle = $('#theme-toggle');
    const body = $('body');
    
    // بارگذاری تم ذخیره شده
    const savedTheme = localStorage.getItem('news-theme') || 'light';
    if (savedTheme === 'dark') {
        body.addClass('dark-theme');
        $('.sun-icon').hide();
        $('.moon-icon').show();
    }
    
    themeToggle.on('click', function() {
        body.toggleClass('dark-theme');
        const isDark = body.hasClass('dark-theme');
        
        if (isDark) {
            $('.sun-icon').hide();
            $('.moon-icon').show();
            localStorage.setItem('news-theme', 'dark');
        } else {
            $('.sun-icon').show();
            $('.moon-icon').hide();
            localStorage.setItem('news-theme', 'light');
        }
    });
    
    // ===== جستجوی زنده (Live Search) =====
    let searchTimeout;
    const searchInput = $('#live-search');
    const searchSubmit = $('#search-submit');
    
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length > 2) {
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 500);
        }
    });
    
    searchSubmit.on('click', function(e) {
        e.preventDefault();
        const query = searchInput.val();
        if (query.length > 0) {
            window.location.href = '?s=' + encodeURIComponent(query);
        }
    });
    
    searchInput.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchSubmit.click();
        }
    });
    
    // ===== تغییر نمای Grid/List =====
    $('.view-btn').on('click', function() {
        const view = $(this).data('view');
        $('.view-btn').removeClass('active');
        $(this).addClass('active');
        
        $('#news-grid').attr('data-view', view);
        
        if (view === 'list') {
            $('.news-card').addClass('list-view');
        } else {
            $('.news-card').removeClass('list-view');
        }
        
        localStorage.setItem('news-view', view);
    });
    
    // بارگذاری نمای ذخیره شده
    const savedView = localStorage.getItem('news-view');
    if (savedView === 'list') {
        $('[data-view="list"]').click();
    }
    
    // ===== بوکمارک اخبار =====
    $('.bookmark-btn').on('click', function(e) {
        e.preventDefault();
        const btn = $(this);
        const postId = btn.data('post-id');
        
        // دریافت بوکمارک‌های ذخیره شده
        let bookmarks = JSON.parse(localStorage.getItem('news-bookmarks') || '[]');
        
        if (bookmarks.includes(postId)) {
            // حذف از بوکمارک
            bookmarks = bookmarks.filter(id => id !== postId);
            btn.removeClass('bookmarked');
        } else {
            // افزودن به بوکمارک
            bookmarks.push(postId);
            btn.addClass('bookmarked');
            showNotification('✅ به علاقه‌مندی‌ها اضافه شد');
        }
        
        localStorage.setItem('news-bookmarks', JSON.stringify(bookmarks));
    });
    
    // بارگذاری وضعیت بوکمارک‌ها
    function loadBookmarks() {
        const bookmarks = JSON.parse(localStorage.getItem('news-bookmarks') || '[]');
        bookmarks.forEach(function(postId) {
            $('.bookmark-btn[data-post-id="' + postId + '"]').addClass('bookmarked');
        });
    }
    loadBookmarks();
    
    // ===== اشتراک‌گذاری =====
    $('.share-item').on('click', function(e) {
        e.preventDefault();
        const shareType = $(this).data('share');
        const newsCard = $(this).closest('.news-card');
        const postId = newsCard.data('post-id');
        const title = newsCard.find('.news-title a').text();
        const url = newsCard.find('.news-title a').attr('href');
        
        switch(shareType) {
            case 'telegram':
                window.open('https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title), '_blank');
                break;
            case 'twitter':
                window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title), '_blank');
                break;
            case 'whatsapp':
                window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + url), '_blank');
                break;
            case 'copy':
                copyToClipboard(url);
                showNotification('🔗 لینک کپی شد');
                break;
        }
    });
    
    // ===== بارگذاری بیشتر (Load More) =====
    let isLoading = false;
    
    $('.load-more-btn').on('click', function() {
        if (isLoading) return;
        
        const btn = $(this);
        const currentPage = parseInt(btn.data('page'));
        const maxPages = parseInt(btn.data('max'));
        
        isLoading = true;
        btn.find('.btn-text').hide();
        btn.find('.btn-loader').show();
        
        // دریافت پارامترهای URL
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('page', currentPage);
        
        $.ajax({
            url: window.location.pathname + '?' + urlParams.toString(),
            method: 'GET',
            success: function(response) {
                const newContent = $(response).find('.news-card');
                
                if (newContent.length > 0) {
                    // اضافه کردن با انیمیشن
                    newContent.each(function(index) {
                        $(this).css({
                            opacity: 0,
                            transform: 'translateY(30px)'
                        });
                        setTimeout(() => {
                            $(this).appendTo('#news-grid').animate({
                                opacity: 1,
                                transform: 'translateY(0)'
                            }, 500);
                        }, index * 100);
                    });
                    
                    // بروزرسانی دکمه
                    if (currentPage < maxPages) {
                        btn.data('page', currentPage + 1);
                        btn.find('.btn-text').show();
                        btn.find('.btn-loader').hide();
                    } else {
                        btn.fadeOut();
                    }
                    
                    // بارگذاری بوکمارک‌ها برای کارت‌های جدید
                    loadBookmarks();
                }
                
                isLoading = false;
            },
            error: function() {
                showNotification('❌ خطا در بارگذاری اخبار');
                btn.find('.btn-text').show();
                btn.find('.btn-loader').hide();
                isLoading = false;
            }
        });
    });
    
    // ===== اسکرول بینهایت (Infinite Scroll) - اختیاری =====
    let infiniteScrollEnabled = false; // فعال/غیرفعال کردن
    
    if (infiniteScrollEnabled) {
        let scrollTimeout;
        $(window).on('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                const scrollPosition = $(window).scrollTop() + $(window).height();
                const documentHeight = $(document).height();
                
                if (scrollPosition > documentHeight - 500 && !isLoading) {
                    if ($('.load-more-btn:visible').length > 0) {
                        $('.load-more-btn').click();
                    }
                }
            }, 200);
        });
    }
    
    // ===== افکت Parallax برای تصاویر =====
    $(window).on('scroll', function() {
        const scrolled = $(window).scrollTop();
        $('.news-image').each(function() {
            const card = $(this).closest('.news-card');
            if (isElementInViewport(card[0])) {
                const cardOffset = card.offset().top;
                const parallaxValue = (scrolled - cardOffset) * 0.1;
                $(this).css('transform', 'translateY(' + parallaxValue + 'px) scale(1.1)');
            }
        });
    });
    
    // ===== تحریک نمایش بازدید (افزایش بازدید) =====
    $('.news-card').on('mouseenter', function() {
        const postId = $(this).data('post-id');
        recordView(postId);
    });
    
    // ===== نوتیفیکیشن ساده =====
    function showNotification(message) {
        const notification = $('<div class="news-notification">' + message + '</div>');
        notification.css({
            position: 'fixed',
            bottom: '24px',
            right: '24px',
            background: 'linear-gradient(135deg, #6366f1, #8b5cf6)',
            color: 'white',
            padding: '16px 24px',
            borderRadius: '12px',
            boxShadow: '0 8px 24px rgba(99, 102, 241, 0.4)',
            fontWeight: '700',
            fontSize: '14px',
            zIndex: 10000,
            opacity: 0,
            transform: 'translateY(20px)',
            transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
        });
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.css({
                opacity: 1,
                transform: 'translateY(0)'
            });
        }, 100);
        
        setTimeout(function() {
            notification.css({
                opacity: 0,
                transform: 'translateY(20px)'
            });
            setTimeout(function() {
                notification.remove();
            }, 400);
        }, 3000);
    }
    
    // ===== توابع کمکی =====
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            const temp = $('<input>');
            $('body').append(temp);
            temp.val(text).select();
            document.execCommand('copy');
            temp.remove();
        }
    }
    
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    function performSearch(query) {
        // می‌تونی اینجا AJAX search اضافه کنی
        console.log('Searching for:', query);
    }
    
    function recordView(postId) {
        // ثبت بازدید - می‌تونی با AJAX به سرور بفرستی
        if (!localStorage.getItem('viewed_' + postId)) {
            localStorage.setItem('viewed_' + postId, 'true');
            
            // ارسال به سرور
            $.post(ajaxurl, {
                action: 'record_post_view',
                post_id: postId
            });
        }
    }
    
    // ===== دریافت نرخ لحظه‌ای رمزارزها =====
    function updateCryptoPrices() {
        $.ajax({
            url: 'https://api.coingecko.com/api/v3/simple/price',
            data: {
                ids: 'bitcoin,ethereum,ripple',
                vs_currencies: 'usd',
                include_24hr_change: true
            },
            success: function(data) {
                let html = '';
                
                if (data.bitcoin) {
                    html += '<div class="price-item">';
                    html += '<span class="crypto-name">بیت‌کوین</span>';
                    html += '<span class="crypto-price">$' + data.bitcoin.usd.toLocaleString() + '</span>';
                    html += '</div>';
                }
                
                if (data.ethereum) {
                    html += '<div class="price-item">';
                    html += '<span class="crypto-name">اتریوم</span>';
                    html += '<span class="crypto-price">$' + data.ethereum.usd.toLocaleString() + '</span>';
                    html += '</div>';
                }
                
                if (data.ripple) {
                    html += '<div class="price-item">';
                    html += '<span class="crypto-name">ریپل</span>';
                    html += '<span class="crypto-price">$' + data.ripple.usd.toFixed(4) + '</span>';
                    html += '</div>';
                }
                
                $('#crypto-prices-sidebar').html(html);
            },
            error: function() {
                $('#crypto-prices-sidebar').html('<p style="color: #64748b; font-size: 13px;">خطا در دریافت قیمت‌ها</p>');
            }
        });
    }
    
    // بروزرسانی قیمت‌ها
    updateCryptoPrices();
    setInterval(updateCryptoPrices, 30000); // هر 30 ثانیه
    
    // ===== افکت‌های بصری =====
    // Lazy loading تصاویر
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(function(img) {
            imageObserver.observe(img);
        });
    }
    
    // ===== کیبورد شورتکات‌ها =====
    $(document).on('keydown', function(e) {
        // ESC برای بستن منوها
        if (e.key === 'Escape') {
            $('.sort-menu, .share-menu').css({
                opacity: 0,
                visibility: 'hidden'
            });
        }
        
        // Ctrl/Cmd + K برای فوکوس روی جستجو
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            $('#live-search').focus();
        }
    });
    
    // ===== اسکرول نرم =====
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 600);
        }
    });
    
    // ===== انیمیشن ورود المان‌ها =====
    const animateOnScroll = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                animateOnScroll.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    document.querySelectorAll('.news-card, .sidebar-widget').forEach(function(el) {
        animateOnScroll.observe(el);
    });
    
    // ===== پیشنمایش سریع خبر (Modal) - اختیاری =====
    let modalEnabled = false; // فعال/غیرفعال
    
    if (modalEnabled) {
        $('.news-card').on('dblclick', function(e) {
            if (!$(e.target).is('a, button')) {
                const title = $(this).find('.news-title a').text();
                const excerpt = $(this).find('.news-excerpt').text();
                const link = $(this).find('.news-title a').attr('href');
                
                const modal = $('<div class="news-modal-overlay"></div>');
                const modalContent = $(`
                    <div class="news-modal">
                        <button class="modal-close">&times;</button>
                        <h2>${title}</h2>
                        <p>${excerpt}</p>
                        <a href="${link}" class="modal-read-more">ادامه مطلب</a>
                    </div>
                `);
                
                modal.append(modalContent).appendTo('body');
                modal.fadeIn(300);
                
                modal.on('click', function(e) {
                    if ($(e.target).is('.news-modal-overlay, .modal-close')) {
                        modal.fadeOut(300, function() {
                            modal.remove();
                        });
                    }
                });
            }
        });
    }
    
    // ===== سیستم رای‌گیری (صعودی/نزولی) =====
    document.querySelectorAll('.news-voting').forEach(voting => {
        const postId = voting.dataset.postId;
        const bullishBtn = voting.querySelector('.bullish-btn');
        const bearishBtn = voting.querySelector('.bearish-btn');

        bullishBtn.addEventListener('click', () => handleVote(postId, 'bullish', bullishBtn, bearishBtn));
        bearishBtn.addEventListener('click', () => handleVote(postId, 'bearish', bearishBtn, bullishBtn));
    });

    function handleVote(postId, voteType, activeBtn, inactiveBtn) {
        // اگر قبلاً رای داده شده
        if (activeBtn.classList.contains('voted')) {
            showNotification('شما قبلاً رای داده‌اید!', 'warning');
            return;
        }

        // اضافه کردن انیمیشن
        activeBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            activeBtn.style.transform = '';
        }, 200);

        // ارسال AJAX
        fetch(newsVoting.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'news_vote',
                post_id: postId,
                vote: voteType,
                nonce: newsVoting.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // آپدیت UI
                activeBtn.classList.add('active', 'voted');
                inactiveBtn.classList.add('voted');
                
                // آپدیت شمارنده‌ها
                const bullishCount = activeBtn.closest('.news-voting').querySelector('.bullish-btn .vote-count');
                const bearishCount = activeBtn.closest('.news-voting').querySelector('.bearish-btn .vote-count');
                
                bullishCount.textContent = data.data.bullish_votes;
                bearishCount.textContent = data.data.bearish_votes;
                
                // نمایش پیام موفقیت
                showNotification('رای شما با موفقیت ثبت شد!', 'success');
            } else {
                showNotification(data.data || 'خطا در ثبت رای', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('خطا در ارتباط با سرور', 'error');
        });
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `vote-notification ${type}`;
        notification.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                ${type === 'success' ? '<polyline points="20 6 9 17 4 12"></polyline>' : 
                  type === 'warning' ? '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>' :
                  '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'}
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);

        // انیمیشن ورود
        setTimeout(() => notification.classList.add('show'), 10);

        // حذف بعد از 3 ثانیه
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // ===== پایان سیستم رای‌گیری =====

    console.log('📰 News Archive Script Loaded Successfully!');
});
