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
<h2><?php echo esc_html( $args['title'] . ' Outputs' ); ?></h2>

<?php
foreach ( $outputs as $output ) {
	$tools           = get_children( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_children
		[
			'post_parent' => $output->ID,
			'post_type'   => ILM_Guide::POST_TYPE,
			'numberposts' => ILM_Guide::POST_LIMIT, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
		]
	);
	$first_paragraph = str_replace( '</p>', ' <a href="' . esc_url( get_permalink( $output->ID ) ) . '">Read More</a></p>', ILM_Guide::get_first_paragraph( $output->post_content ) );
	?>

<section class='output'>
	<h3><?php echo esc_html( $output->post_title ); ?></h3>
	<?php echo wp_kses_post( $first_paragraph ); ?>

	<details>
		<summary><h4>Suggested tools that can help achieve this output</h4></summary>
		<ol>

	<?php
	foreach ( $tools as $tool ) {
		?>
				<li><a href="<?php echo esc_url( get_the_permalink( $tool->ID ) ); ?>'"><?php echo esc_html( $tool->post_title ); ?></a></li>
			<?php
	}
	?>

				</ol>
	</details>
</section>
<?php } // section ?>
