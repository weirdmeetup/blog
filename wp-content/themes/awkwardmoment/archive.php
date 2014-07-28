<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package awkwardmoment
 */

get_header(); ?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="page-header archive-header">
				<h1 class="page-title">
					<?php
						if ( is_category() ) :
							echo "Category : ";
							single_cat_title();

						elseif ( is_tag() ) :
							echo "Tag : ";
							single_tag_title();

						elseif ( is_author() ) :
							printf( __( 'Author: %s', 'awkwardmoment' ), '<span class="vcard">' . get_the_author() . '</span>' );

						elseif ( is_day() ) :
							printf( __( 'Day: %s', 'awkwardmoment' ), '<span>' . get_the_date() . '</span>' );

						elseif ( is_month() ) :
							printf( __( 'Month: %s', 'awkwardmoment' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'awkwardmoment' ) ) . '</span>' );

						elseif ( is_year() ) :
							printf( __( 'Year: %s', 'awkwardmoment' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'awkwardmoment' ) ) . '</span>' );

						elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
							_e( 'Asides', 'awkwardmoment' );

						elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) :
							_e( 'Galleries', 'awkwardmoment');

						elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
							_e( 'Images', 'awkwardmoment');

						elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
							_e( 'Videos', 'awkwardmoment' );

						elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :
							_e( 'Quotes', 'awkwardmoment' );

						elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
							_e( 'Links', 'awkwardmoment' );

						elseif ( is_tax( 'post_format', 'post-format-status' ) ) :
							_e( 'Statuses', 'awkwardmoment' );

						elseif ( is_tax( 'post_format', 'post-format-audio' ) ) :
							_e( 'Audios', 'awkwardmoment' );

						elseif ( is_tax( 'post_format', 'post-format-chat' ) ) :
							_e( 'Chats', 'awkwardmoment' );

						else :
							_e( 'Archives', 'awkwardmoment' );

						endif;
					?>
				</h1>
				<?php
					// Show an optional term description.
					$term_description = term_description();
					if ( ! empty( $term_description ) ) :
						printf( '<div class="taxonomy-description">%s</div>', $term_description );
					endif;
				?>
			</header><!-- .page-header -->

			<div class="summaries-wrapper">
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content-summary', get_post_format() );
				?>

			<?php endwhile; ?>

		</div>
		<?php awkwardmoment_paging_nav(); ?>
		<?php else : ?>

			<?php get_template_part( 'content', 'none' ); ?>

		<?php endif; ?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
