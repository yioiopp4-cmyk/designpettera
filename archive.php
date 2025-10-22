<?php
/**
 * آرشیو اخبار
 *
 * @package CryptoSekhyab
 */

get_header('arzdigital');
?>

<main class="news-archive">
    
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo home_url(); ?>">خانه</a>
                <span>/</span>
                <span>اخبار</span>
            </div>
            
            <h1>📰 آخرین اخبار و تحلیل‌ها</h1>
            <p>جدیدترین اخبار دنیای ارزهای دیجیتال</p>
        </div>
    </div>

    <!-- اخبار -->
    <div class="news-content">
        <div class="container">
            <div class="news-grid">
                <?php
                if (have_posts()) :
                    while (have_posts()) : the_post();
                ?>
                    <article class="news-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="news-image">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php the_post_thumbnail_url('large'); ?>" alt="<?php the_title_attribute(); ?>">
                                </a>
                                <div class="news-category">
                                    <?php 
                                    $cats = get_the_category();
                                    echo $cats ? esc_html($cats[0]->name) : 'اخبار';
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="news-body">
                            <div class="news-meta">
                                <span class="news-date">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                        <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M7 3.5V7L9 9" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <?php echo get_the_date('d F Y'); ?>
                                </span>
                                <span class="news-author">
                                    نویسنده: <?php the_author(); ?>
                                </span>
                            </div>
                            
                            <h2 class="news-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <p class="news-excerpt">
                                <?php echo wp_trim_words(get_the_excerpt(), 25); ?>
                            </p>
                            
                            <a href="<?php the_permalink(); ?>" class="news-link">
                                ادامه مطلب →
                            </a>
                        </div>
                    </article>
                <?php 
                    endwhile;
                else : ?>
                    <div class="no-posts">
                        <span>📝</span>
                        <h3>هنوز خبری منتشر نشده</h3>
                        <p>به زودی اخبار جدید منتشر خواهد شد</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if (have_posts()) : ?>
            <div class="pagination">
                <?php
                echo paginate_links(array(
                    'prev_text' => '← قبلی',
                    'next_text' => 'بعدی →',
                    'type' => 'list',
                ));
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<style>
.news-archive {
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

.news-content {
    padding: 60px 0;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-bottom: 60px;
}

.news-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.news-image {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s;
}

.news-card:hover .news-image img {
    transform: scale(1.1);
}

.news-category {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #667eea;
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.news-body {
    padding: 24px;
}

.news-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
    font-size: 13px;
    color: #64748b;
}

.news-date,
.news-author {
    display: flex;
    align-items: center;
    gap: 6px;
}

.news-title {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 12px 0;
}

.news-title a {
    color: #0f172a;
    text-decoration: none;
    transition: color 0.3s;
}

.news-title a:hover {
    color: #667eea;
}

.news-excerpt {
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 16px 0;
}

.news-link {
    color: #667eea;
    font-weight: 700;
    text-decoration: none;
    transition: gap 0.3s;
}

.news-link:hover {
    color: #5e72e4;
}

.no-posts {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
}

.no-posts span {
    font-size: 80px;
    display: block;
    margin-bottom: 24px;
}

.no-posts h3 {
    font-size: 28px;
    margin: 0 0 12px 0;
    color: #0f172a;
}

.no-posts p {
    color: #64748b;
    font-size: 16px;
}

.pagination {
    display: flex;
    justify-content: center;
}

.pagination ul {
    display: flex;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination a,
.pagination span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0 16px;
    background: white;
    color: #0f172a;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    transition: all 0.3s;
}

.pagination a:hover,
.pagination .current {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

@media (max-width: 1024px) {
    .news-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header h1 {
        font-size: 28px;
    }
    
    .news-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php get_footer(); ?>
