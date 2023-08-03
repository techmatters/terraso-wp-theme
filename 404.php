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

			<img
					src="<?php echo esc_url( get_template_directory_uri() . '/assets/svg/404.svg' ); ?>" alt=""
			/>

			<header class="zak-content-header">
				<p><?php esc_html_e( 'Oops! Page Not Found', 'zakra' ); ?></p>
			</header>

			<div class="zak-page-content">
				<p>
					<?php esc_html_e( 'We’re sorry, the page you requested could not be found. Please go back to the homepage', 'zakra' ); ?>
				</p>
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
