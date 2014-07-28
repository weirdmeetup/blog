<?php
/**
 * @package awkwardmoment
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('single'); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">
			<?php awkwardmoment_posted_on(); ?>
	    <?php $original = get_post_meta( get_the_ID(), 'syndication_permalink', true);
	      if($original != ""){
	        ?>
	        <div><a class="view-original" target="_blank" href="<?php echo $original;?>">[원본보기]</a></div>
	        <?php
	      }
	    ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'awkwardmoment' ),
				'after'  => '</div>',
			) );
		?>
		
		<!-- @haruair breadtrend -->
		<div class="recommend-article-area">
			<h2 class="heading">Related Articles</h2>
			<div id="recommend-article"></div>
			<script src="http://b.readtrend.com/j/r.js"></script>
		</div>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php
			/* translators: used between list items, there is a space after the comma */
			$category_list = get_the_category_list( __( ', ', 'awkwardmoment' ) );

			/* translators: used between list items, there is a space after the comma */
			$tag_list = get_the_tag_list( '', __( ', ', 'awkwardmoment' ) );

			if ( ! awkwardmoment_categorized_blog() ) {
				// This blog only has 1 category so we just need to worry about tags in the meta text
				if ( '' != $tag_list ) {
					$meta_text = __( 'This entry was tagged %2$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'awkwardmoment' );
				} else {
					$meta_text = __( 'Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'awkwardmoment' );
				}

			} else {
				// But this blog has loads of categories so we should probably display them here
				if ( '' != $tag_list ) {
					$meta_text = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'awkwardmoment' );
				} else {
					$meta_text = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" rel="bookmark">permalink</a>.', 'awkwardmoment' );
				}

			} // end check for categories on this blog

			printf(
				$meta_text,
				$category_list,
				$tag_list,
				get_permalink()
			);
		?>

		<?php edit_post_link( __( 'Edit', 'awkwardmoment' ), '<span class="edit-link">', '</span>' ); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
