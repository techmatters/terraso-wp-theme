<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link    https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package zakra
 */

get_header();
?>

	<main id="zak-primary" class="zak-primary">
		<?php echo apply_filters( 'zakra_after_primary_start_filter', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<section class="zak-error-404 not-found">
			<?php if ( 'page-header' !== zakra_page_title_position() ) : ?>
				<header class="page-header">
					<h1 class="page-title zak-page-content__title">
						<?php echo wp_kses_post( zakra_get_title() ); ?>
					</h1>
				</header><!-- .page-header -->
			<?php endif; ?>

			<div class="zak-page-content zak-no-results">
				<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links above or a search?', 'terraso' ); ?></p>

				<?php get_search_form(); ?>

				<p><img src="/wp-content/uploads/2021/04/man-looking-through-binoculars.jpg"></p>
			</div><!-- .zak-page-content -->

			<a class="zak-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span>
				<?php zakra_get_icon( 'arrow-left-long' ); ?>
				<?php esc_html_e( 'Go Back', 'zakra' ); ?>
			</span>
			</a><!-- .button -->
		</section><!-- .zak-error-404 -->

		<?php echo apply_filters( 'zakra_after_primary_end_filter', false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</main><!-- /.zak-primary -->

<?php
get_sidebar();
get_footer();
