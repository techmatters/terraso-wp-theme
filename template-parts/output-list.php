<?php
/**
 * ILM Guide output and tool list (for Element page).
 *
 * @package Terraso
 */

$outputs = get_children( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_children
	[
		'post_parent' => $args['id'],
		'post_type'   => ILM_Guide::POST_TYPE,
		'numberposts' => ILM_Guide::POST_LIMIT, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
	]
);
?>
<article class='guide-outputs'>
	<h2>
	<?php
			echo wp_kses_post( ILM_Guide::get_section_image() );
			echo esc_html( $args['title'] . ' ' . __( 'Outputs' ) );
	?>
			</h2>
	<div class='output-wrapper'>
	<?php
	foreach ( $outputs as $output ) {
		$tools           = get_children( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_children
			[
				'post_parent' => $output->ID,
				'post_type'   => ILM_Guide::POST_TYPE,
				'numberposts' => ILM_Guide::POST_LIMIT, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
			]
		);
		$output_link     = get_permalink( $output->ID );
		$first_paragraph = str_replace( '</p>', ' <a class="read-more" href="' . esc_url( $output_link ) . '">Read More</a></p>', ILM_Guide::get_first_paragraph( $output->post_content ) );
		?>

	<section class='output'>
		<h3><a href="<?php echo esc_url( $output_link ); ?>"><?php echo esc_html( $output->post_title ); ?></a></h3>
		<?php echo wp_kses_post( $first_paragraph ); ?>

		<details>
			<summary><h4>Suggested tools that can help achieve this output</h4></summary>
			<ol>

		<?php
		foreach ( $tools as $tool ) {
			?>
					<li><a href="<?php echo esc_url( get_post_meta( $tool->ID, ILM_Guide::TOOL_URL_META_KEY, true ) ); ?>"><?php echo esc_html( $tool->post_title ); ?></a></li>
				<?php
		}
		?>

					</ol>
		</details>
	</section>
	<?php } // section ?>
	</div>
</article>
