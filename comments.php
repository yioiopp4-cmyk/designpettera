<?php
/**
 * Template for displaying comments
 *
 * @package CryptoSekhyab
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area comments-modern">
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            $comments_number = get_comments_number();
            printf(
                _n('%s دیدگاه', '%s دیدگاه', $comments_number, 'crypto-sekhyab'),
                number_format_i18n($comments_number)
            );
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 56,
            ));
            ?>
        </ol>

        <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
            <nav class="comment-navigation">
                <div class="nav-previous"><?php previous_comments_link('دیدگاه‌های قدیمی‌تر'); ?></div>
                <div class="nav-next"><?php next_comments_link('دیدگاه‌های جدیدتر'); ?></div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments">دیدگاه‌ها بسته شده‌اند.</p>
    <?php endif; ?>

    <?php
    comment_form(array(
        'title_reply'          => 'دیدگاه خود را بنویسید',
        'label_submit'         => 'ارسال دیدگاه',
        'class_submit'         => 'btn btn-primary',
        'comment_field'        => '<p class="comment-form-comment"><textarea id="comment" name="comment" rows="5" placeholder="نظر شما..." required></textarea></p>',
        'fields'               => array(
            'author' => '<p class="comment-form-author"><input id="author" name="author" type="text" placeholder="نام" /></p>',
            'email'  => '<p class="comment-form-email"><input id="email" name="email" type="email" placeholder="ایمیل" /></p>',
            'url'    => '<p class="comment-form-url"><input id="url" name="url" type="url" placeholder="وب‌سایت (اختیاری)" /></p>',
        ),
        'class_form'           => 'comment-form-modern',
        'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
        'comment_notes_before' => '',
        'comment_notes_after'  => '',
    ));
    ?>
</div>

<style>
.comments-modern { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-top:24px; }
.comments-modern .comments-title { font-size:20px; font-weight:900; color:#0f172a; margin:0 0 16px 0; }
.comments-modern .comment-list { list-style:none; margin:0; padding:0; }
.comments-modern .comment { border-bottom:1px solid #f1f5f9; padding:16px 0; }
.comments-modern .comment:last-child { border-bottom:none; }
.comments-modern .comment-body { display:grid; grid-template-columns:48px 1fr; gap:12px; align-items:flex-start; }
.comments-modern .comment-author .avatar { width:48px; height:48px; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.comments-modern .comment-meta { display:flex; gap:8px; align-items:center; font-size:13px; color:#64748b; margin-bottom:6px; }
.comments-modern .fn { font-weight:800; color:#0f172a; }
.comments-modern .comment-content { font-size:15px; line-height:1.9; color:#334155; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; }
.comments-modern .reply { margin-top:8px; }
.comments-modern .reply a { font-weight:800; color:#667eea; text-decoration:none; }

.comment-form-modern { margin-top:24px; }
.comment-form-modern input[type="text"],
.comment-form-modern input[type="email"],
.comment-form-modern input[type="url"],
.comment-form-modern textarea { width:100%; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; font-size:14px; color:#0f172a; outline:none; }
.comment-form-modern textarea { min-height:120px; }
.comment-form-modern input:focus,
.comment-form-modern textarea:focus { border-color:#c7d2fe; box-shadow:0 0 0 3px rgba(199,210,254,0.4); }
.comment-form-modern .form-submit { margin:0; }
.comment-form-modern .btn.btn-primary, .comment-form-modern input[type="submit"] { background:#667eea; color:#fff; border:none; border-radius:10px; padding:10px 18px; font-weight:800; cursor:pointer; }
.comment-form-modern .btn.btn-primary:hover, .comment-form-modern input[type="submit"]:hover { background:#5568d3; }

@media (max-width: 768px) {
  .comments-modern { padding:16px; }
}
</style>
