<?php
/**
 * قالب نمایش تک پست
 *
 * @package CryptoSekhyab
 */

get_header('arzdigital'); ?>

<main id="main-content" class="site-main">
    <div class="container">
        
        <?php while (have_posts()) : the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('main-single-post'); ?>>
                
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                    
                    <div class="entry-meta">
                        <span class="posted-on">
                            <i class="dashicons dashicons-calendar"></i>
                            <?php echo get_the_date(); ?>
                        </span>
                        <span class="byline">
                            <i class="dashicons dashicons-admin-users"></i>
                            نویسنده: <?php the_author(); ?>
                        </span>
                        <?php if (has_category()) : ?>
                            <span class="cat-links">
                                <i class="dashicons dashicons-category"></i>
                                <?php the_category(', '); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail('crypto-large'); ?>
                    </div>
                <?php endif; ?>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
                
                <?php if (has_tag()) : ?>
                    <footer class="entry-footer">
                        <div class="tags-links">
                            <i class="dashicons dashicons-tag"></i>
                            برچسب‌ها: <?php the_tags('', ', ', ''); ?>
                        </div>
                    </footer>
                <?php endif; ?>
                
            </article>
            
            <?php
            // نمایش اخبار مرتبط با دسته یا تگ
            $categories = get_the_category(get_the_ID());
            $tags = get_the_tags(get_the_ID());
            
            $related_query_args = array(
                'post__not_in'   => array(get_the_ID()),
                'posts_per_page' => 3,
                'post_status'    => 'publish',
            );
            
            // اگر دسته دارد، از دسته استفاده کن
            if (!empty($categories)) {
                $related_query_args['category__in'] = wp_list_pluck($categories, 'term_id');
            }
            // اگر تگ دارد، از تگ استفاده کن
            elseif (!empty($tags)) {
                $related_query_args['tag__in'] = wp_list_pluck($tags, 'term_id');
            }
            
            $related = new WP_Query($related_query_args);
            
            if ($related->have_posts()) :
                ?>
                <div class="related-posts">
                    <h2 class="section-title">اخبار مرتبط</h2>
                    <div class="posts-grid">
                        <?php while ($related->have_posts()) : $related->the_post(); ?>
                            <article class="post-card">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('news-card'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <h3 class="post-title">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>
                                    <div class="post-meta">
                                        <span><?php echo get_the_date(); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php
            // نمایش کامنت‌ها
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
            
        <?php endwhile; ?>
        
    </div>
</main>

<?php get_footer(); ?>
