<?php
/**
 * @package awkwardmoment
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('summary'); ?>>
  <div class="summary-wrapper">
  <header class="entry-header">
    <h1 class="entry-title">
    <?php the_title( sprintf( '<a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a>' ); ?>
    <?php $original = get_post_meta( get_the_ID(), 'syndication_permalink', true);
      if($original != ""){
        ?>
        <a class="view-original" target="_blank" href="<?php echo $original;?>">[원본보기]</a>
        <?php
      }
    ?>
    </h1>
  </header><!-- .entry-header -->

  <div class="entry-summary">
    <?php the_summary(); ?>
  </div><!-- .entry-summary -->

  <footer class="entry-footer">
    <?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
      <?php
        /* translators: used between list items, there is a space after the comma 
        $categories_list = get_the_category_list( __( ', ', 'awkwardmoment' ) );
        if ( $categories_list && awkwardmoment_categorized_blog() ) :
      ?>
      <span class="cat-links">
        <?php printf( __( 'Posted in %1$s', 'awkwardmoment' ), $categories_list ); ?>
      </span>
      <?php endif; // End if categories */ ?>

      <?php if ( 'post' == get_post_type() ) : ?>
      <div class="entry-meta">
        <?php awkwardmoment_posted_on(); ?>
        <!--@minieetea add gravatar icon-->
        <a class="gravatar" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"> <?php echo get_avatar( get_the_author_meta('ID'), 68, $default, $alt ); ?></a>
      </div><!-- .entry-meta -->
      <?php endif; ?>
      <?php
        /* translators: used between list items, there is a space after the comma */
        $tags_list = get_the_tag_list( '', __( '', 'awkwardmoment' ) );
        if ( $tags_list ) :
      ?>
      <span class="tags-links">
        <?php printf( __( '%1$s', 'awkwardmoment' ), $tags_list ); ?>
      </span>
      <?php endif; // End if $tags_list ?>
    <?php endif; // End if 'post' == get_post_type() ?>

    <?php /*if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
    <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'awkwardmoment' ), __( '1 Comment', 'awkwardmoment' ), __( '% Comments', 'awkwardmoment' ) ); ?></span>
    <?php endif; */ ?>

    <?php // edit_post_link( __( 'Edit', 'awkwardmoment' ), '<span class="edit-link">', '</span>' ); ?>
  </footer><!-- .entry-footer -->
  </div>
</article><!-- #post-## -->
