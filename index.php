<?php
/**
 * قالب اصلی - Index
 *
 * @package CryptoSekhyab
 */

get_header('arzdigital'); ?>

<main id="main-content" class="site-main">
    <div class="container">
        
        <?php if (have_posts()) : ?>
            
            <div class="posts-grid">
                <?php while (have_posts()) : the_post(); ?>
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card fade-in'); ?>>
                        
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('news-card'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>
                            
                            <div class="post-meta">
                                <span class="post-date">
                                    <?php echo get_the_date(); ?>
                                </span>
                                <span class="post-author">
                                    توسط <?php the_author(); ?>
                                </span>
                            </div>
                            
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                ادامه مطلب ←
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
                <h2>مطلبی یافت نشد</h2>
                <p>متأسفانه هیچ مطلبی برای نمایش وجود ندارد.</p>
            </div>
            
        <?php endif; ?>
        
    </div>
</main>

<?php get_footer(); ?>
