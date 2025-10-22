<?php
/**
 * قالب آرشیو ارزهای دیجیتال
 *
 * @package CryptoSekhyab
 */

get_header('arzdigital'); ?>

<div class="crypto-archive-header">
    <div class="container">
        <h1>لیست ارزهای دیجیتال</h1>
        <p>قیمت لحظه‌ای و اطلاعات کامل ارزهای دیجیتال</p>
    </div>
</div>

<main id="main-content" class="site-main">
    <div class="container">
        
        <?php
        // نمایش لیست ارزها با شورت‌کد
        echo do_shortcode('[crypto_list limit="20"]');
        ?>
        
        <?php if (have_posts()) : ?>
            
            <div class="posts-grid mt-40">
                <?php while (have_posts()) : the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card fade-in'); ?>>
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('crypto-thumbnail'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            
                            <?php
                            $symbol = get_post_meta(get_the_ID(), '_crypto_symbol', true);
                            if ($symbol) : ?>
                                <div class="crypto-symbol-badge">
                                    <?php echo esc_html($symbol); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                مشاهده جزئیات ←
                            </a>
                        </div>
                        
                    </article>
                    
                <?php endwhile; ?>
            </div>
            
            <div class="pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('→ قبلی', 'crypto-sekhyab'),
                    'next_text' => __('بعدی ←', 'crypto-sekhyab'),
                ));
                ?>
            </div>
            
        <?php else : ?>
            
            <div class="no-posts">
                <h2>هیچ ارزی یافت نشد</h2>
                <p>در حال حاضر هیچ ارز دیجیتالی برای نمایش وجود ندارد.</p>
            </div>
            
        <?php endif; ?>
        
    </div>
</main>

<?php get_footer(); ?>
