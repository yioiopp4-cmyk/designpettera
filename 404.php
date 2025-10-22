<?php
/**
 * صفحه 404 - صفحه پیدا نشد
 *
 * @package CryptoSekhyab
 */

get_header('arzdigital'); ?>

<main id="main-content" class="site-main">
    <div class="container">
        
        <div class="error-404">
            <h1>404</h1>
            <h2>صفحه مورد نظر یافت نشد!</h2>
            <p>متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد یا حذف شده است.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn">
                بازگشت به صفحه اصلی
            </a>
            
            <div class="search-404" style="margin-top: 50px; max-width: 500px; margin-left: auto; margin-right: auto;">
                <?php get_search_form(); ?>
            </div>
            
            <?php
            // نمایش آخرین مطالب
            $recent_posts = new WP_Query(array(
                'posts_per_page' => 3,
                'post_status'    => 'publish',
            ));
            
            if ($recent_posts->have_posts()) :
                ?>
                <div class="recent-posts-404" style="margin-top: 60px;">
                    <h3 style="text-align: center; margin-bottom: 30px;">آخرین مطالب</h3>
                    <div class="posts-grid" style="max-width: 900px; margin: 0 auto;">
                        <?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
                            <article class="post-card">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('news-card'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <h4 class="post-title">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h4>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</main>

<?php get_footer(); ?>
