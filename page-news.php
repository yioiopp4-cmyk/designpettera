<?php
/**
 * Template Name: صفحه اخبار پیشرفته
 * Description: قالب حرفه‌ای اخبار با فیلتر، جستجو و امکانات پیشرفته
 */

get_header('arzdigital');

// دریافت پارامترهای URL
$current_category = isset($_GET['cat']) ? sanitize_text_field($_GET['cat']) : 'all';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'latest';
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// دریافت دسته‌بندی‌ها از تنظیمات قالب
$categories_from_settings = get_option('crypto_sekhyab_news_categories', array(
    array('slug' => 'all', 'name' => 'همه اخبار', 'icon' => '📰', 'color' => '#6366f1'),
    array('slug' => 'bitcoin', 'name' => 'بیت‌کوین', 'icon' => '₿', 'color' => '#f7931a'),
    array('slug' => 'ethereum', 'name' => 'اتریوم', 'icon' => '◈', 'color' => '#627eea'),
    array('slug' => 'altcoin', 'name' => 'آلت‌کوین‌ها', 'icon' => '🪙', 'color' => '#10b981'),
    array('slug' => 'analysis', 'name' => 'تحلیل', 'icon' => '📊', 'color' => '#8b5cf6'),
    array('slug' => 'global', 'name' => 'اخبار جهانی', 'icon' => '🌍', 'color' => '#06b6d4'),
    array('slug' => 'market', 'name' => 'بازار', 'icon' => '📈', 'color' => '#ec4899'),
));

// تبدیل آرایه به فرمت قابل استفاده
$categories = array();
foreach ($categories_from_settings as $cat) {
    $categories[$cat['slug']] = array(
        'name' => $cat['name'],
        'icon' => $cat['icon'],
        'color' => $cat['color']
    );
}

// ساخت Query
$args = array(
    'post_type' => 'post',
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => 'publish',
);

// فیلتر دسته‌بندی - بهبود یافته
if ($current_category !== 'all') {
    // جستجو در دسته‌بندی‌ها و تگ‌ها
    $args['tax_query'] = array(
        'relation' => 'OR',
        array(
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $current_category,
        ),
        array(
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => $current_category,
        ),
    );
}

// جستجو
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// مرتب‌سازی
switch ($sort_by) {
    case 'popular':
        $args['meta_key'] = 'post_views_count';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'liked':
        $args['meta_key'] = 'post_likes_count';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;
    case 'oldest':
        $args['orderby'] = 'date';
        $args['order'] = 'ASC';
        break;
    default: // latest
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
}

$news_query = new WP_Query($args);
$total_pages = $news_query->max_num_pages;
?>

<main class="news-archive-page">
    <div class="news-container">
        
        <!-- هدر و نوار ابزار -->
        <div class="news-header-section">
            <div class="page-title-area">
                <h1 class="page-main-title">📰 اخبار و تحلیل‌های رمزارزها</h1>
                <p class="page-subtitle">به‌روزترین اخبار دنیای کریپتو را دنبال کنید</p>
            </div>

            <!-- نوار ابزار -->
            <div class="news-toolbar">
                <div class="toolbar-left">
                    <!-- جستجو -->
                    <div class="search-box">
                        <input type="text" id="live-search" placeholder="جستجوی اخبار..." value="<?php echo esc_attr($search_query); ?>">
                        <button class="search-btn" id="search-submit">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- مرتب‌سازی -->
                    <div class="sort-dropdown">
                        <button class="sort-btn" id="sort-trigger">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18M7 12h10M11 18h2"></path>
                            </svg>
                            <span>مرتب‌سازی</span>
                        </button>
                        <div class="sort-menu" id="sort-menu">
                            <a href="?sort=latest&cat=<?php echo $current_category; ?>" class="sort-item <?php echo $sort_by === 'latest' ? 'active' : ''; ?>">جدیدترین</a>
                            <a href="?sort=popular&cat=<?php echo $current_category; ?>" class="sort-item <?php echo $sort_by === 'popular' ? 'active' : ''; ?>">پربازدیدترین</a>
                            <a href="?sort=liked&cat=<?php echo $current_category; ?>" class="sort-item <?php echo $sort_by === 'liked' ? 'active' : ''; ?>">محبوب‌ترین</a>
                            <a href="?sort=oldest&cat=<?php echo $current_category; ?>" class="sort-item <?php echo $sort_by === 'oldest' ? 'active' : ''; ?>">قدیمی‌ترین</a>
                        </div>
                    </div>
                </div>

                <div class="toolbar-right">
                    <!-- تغییر نما -->
                    <div class="view-toggle">
                        <button class="view-btn active" data-view="grid">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                            </svg>
                        </button>
                        <button class="view-btn" data-view="list">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- حالت شب/روز -->
                    <button class="theme-toggle" id="theme-toggle">
                        <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                        <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- فیلتر دسته‌بندی‌ها -->
        <div class="categories-filter">
            <div class="categories-scroll">
                <?php foreach ($categories as $slug => $cat) : ?>
                    <a href="?cat=<?php echo $slug; ?>&sort=<?php echo $sort_by; ?>" 
                       class="category-tab <?php echo $current_category === $slug ? 'active' : ''; ?>"
                       style="--cat-color: <?php echo $cat['color']; ?>">
                        <span class="cat-icon"><?php echo $cat['icon']; ?></span>
                        <span class="cat-name"><?php echo $cat['name']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Grid اخبار -->
        <div class="news-grid" id="news-grid" data-view="grid">
            <?php if ($news_query->have_posts()) : ?>
                <?php while ($news_query->have_posts()) : $news_query->the_post(); 
                    $post_id = get_the_ID();
                    $views = get_post_meta($post_id, 'post_views_count', true) ?: 0;
                    $likes = get_post_meta($post_id, 'post_likes_count', true) ?: 0;
                    $comments_count = get_comments_number($post_id);
                    $reading_time = ceil(str_word_count(strip_tags(get_the_content())) / 200);
                ?>
                    <article class="news-card" data-post-id="<?php echo $post_id; ?>">
                        <div class="news-card-inner">
                            <!-- تصویر -->
                            <div class="news-image-wrapper">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" 
                                         alt="<?php the_title(); ?>" 
                                         class="news-image">
                                <?php else : ?>
                                    <div class="news-image-placeholder">📰</div>
                                <?php endif; ?>
                                
                                <!-- آیکون دسته روی تصویر -->
                                <?php 
                                $post_categories = get_the_category();
                                if (!empty($post_categories)) :
                                    $first_cat = $post_categories[0];
                                    $cat_slug = $first_cat->slug;
                                    if (isset($categories[$cat_slug])) :
                                ?>
                                    <div class="category-badge" style="background: <?php echo $categories[$cat_slug]['color']; ?>">
                                        <?php echo $categories[$cat_slug]['icon']; ?>
                                    </div>
                                <?php endif; endif; ?>

                                <!-- دکمه بوکمارک -->
                                <button class="bookmark-btn" data-post-id="<?php echo $post_id; ?>">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- محتوا -->
                            <div class="news-content">
                                <h2 class="news-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <p class="news-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20); ?>
                                </p>

                                <!-- متا -->
                                <div class="news-meta">
                                    <div class="meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' پیش'; ?></span>
                                    </div>
                                    
                                    <div class="meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span><?php echo number_format_i18n($views); ?></span>
                                    </div>

                                    <div class="meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        <span><?php echo number_format_i18n($likes); ?></span>
                                    </div>

                                    <div class="meta-item">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                        <span><?php echo number_format_i18n($comments_count); ?></span>
                                    </div>
                                </div>
                                
                                <!-- دکمه‌های رای‌گیری (صعودی/نزولی) -->
                                <?php
                                $votes = crypto_get_vote_counts($post_id);
                                $has_voted = crypto_has_user_voted($post_id);
                                ?>
                                <div class="news-voting" data-post-id="<?php echo $post_id; ?>">
                                    <button class="vote-btn bullish-btn <?php echo $has_voted ? 'voted' : ''; ?>" data-vote="bullish">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="18 15 12 9 6 15"></polyline>
                                        </svg>
                                        <span class="vote-label">صعودی</span>
                                        <span class="vote-count"><?php echo number_format_i18n($votes['bullish']); ?></span>
                                    </button>
                                    <button class="vote-btn bearish-btn <?php echo $has_voted ? 'voted' : ''; ?>" data-vote="bearish">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                        <span class="vote-label">نزولی</span>
                                        <span class="vote-count"><?php echo number_format_i18n($votes['bearish']); ?></span>
                                    </button>
                                </div>

                                <!-- اکشن‌ها -->
                                <div class="news-actions">
                                    <a href="<?php the_permalink(); ?>" class="read-more-btn">
                                        <span>مطالعه کامل</span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>

                                    <div class="share-dropdown">
                                        <button class="share-btn">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="18" cy="5" r="3"></circle>
                                                <circle cx="6" cy="12" r="3"></circle>
                                                <circle cx="18" cy="19" r="3"></circle>
                                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                                            </svg>
                                        </button>
                                        <div class="share-menu">
                                            <a href="#" class="share-item" data-share="telegram">تلگرام</a>
                                            <a href="#" class="share-item" data-share="twitter">توییتر</a>
                                            <a href="#" class="share-item" data-share="whatsapp">واتساپ</a>
                                            <a href="#" class="share-item" data-share="copy">کپی لینک</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
                
                <!-- بارگذاری بیشتر -->
                <?php if ($total_pages > 1) : ?>
                    <div class="load-more-section">
                        <?php if ($paged < $total_pages) : ?>
                            <button class="load-more-btn" data-page="<?php echo $paged + 1; ?>" data-max="<?php echo $total_pages; ?>">
                                <span class="btn-text">نمایش اخبار بیشتر</span>
                                <span class="btn-loader" style="display:none;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="2" x2="12" y2="6"></line>
                                        <line x1="12" y1="18" x2="12" y2="22"></line>
                                        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                        <line x1="2" y1="12" x2="6" y2="12"></line>
                                        <line x1="18" y1="12" x2="22" y2="12"></line>
                                        <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                        <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                    </svg>
                                </span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="no-results">
                    <div class="no-results-icon">😕</div>
                    <h3>خبری یافت نشد</h3>
                    <p>
                        <?php if ($current_category !== 'all') : ?>
                            در دسته‌بندی "<?php echo esc_html($categories[$current_category]['name'] ?? $current_category); ?>" خبری وجود ندارد.
                            <br>
                            لطفاً دسته‌بندی دیگری را انتخاب کنید یا 
                            <a href="<?php echo remove_query_arg(array('cat', 's', 'sort')); ?>" style="color: #6366f1; text-decoration: underline;">همه اخبار</a> را مشاهده کنید.
                        <?php elseif (!empty($search_query)) : ?>
                            نتیجه‌ای برای "<?php echo esc_html($search_query); ?>" یافت نشد.
                            <br>
                            کلمات دیگری را جستجو کنید یا 
                            <a href="<?php echo remove_query_arg('s'); ?>" style="color: #6366f1; text-decoration: underline;">همه اخبار</a> را مشاهده کنید.
                        <?php else : ?>
                            لطفاً فیلترها یا کلمات جستجو را تغییر دهید
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
        </div>

        <!-- سایدبار اخبار برتر -->
        <aside class="news-sidebar">
            <div class="sidebar-widget">
                <h3 class="widget-title">🔥 اخبار برتر هفته</h3>
                <div class="top-news-list">
                    <?php
                    $top_news = new WP_Query(array(
                        'post_type' => 'post',
                        'posts_per_page' => 5,
                        'meta_key' => 'post_views_count',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC',
                        'date_query' => array(
                            array(
                                'after' => '1 week ago'
                            )
                        )
                    ));
                    
                    $rank = 1;
                    while ($top_news->have_posts()) : $top_news->the_post();
                    ?>
                        <a href="<?php the_permalink(); ?>" class="top-news-item">
                            <span class="news-rank"><?php echo $rank; ?></span>
                            <div class="top-news-content">
                                <h4><?php echo wp_trim_words(get_the_title(), 10); ?></h4>
                                <span class="news-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' پیش'; ?></span>
                            </div>
                        </a>
                    <?php 
                        $rank++;
                    endwhile; 
                    wp_reset_postdata();
                    ?>
                </div>
            </div>

            <!-- نرخ لحظه‌ای -->
            <div class="sidebar-widget crypto-prices-widget">
                <h3 class="widget-title">💹 نرخ لحظه‌ای</h3>
                <div class="crypto-prices" id="crypto-prices-sidebar">
                    <div class="price-item">
                        <span class="crypto-name">بیت‌کوین</span>
                        <span class="crypto-price">در حال بارگذاری...</span>
                    </div>
                </div>
            </div>
        </aside>

    </div>
</main>

<?php get_footer(); ?>
