<?php
/**
 * ILM Guide tool list (for Output page).
 *
 * @package Terraso
 */

?>
<h2><?php echo esc_html( $args['title'] . ' Tools' ); ?></h2>

<?php
	$tools = get_children( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_children
		[
			'post_parent' => $args['id'],
			'post_type'   => ILM_Guide::POST_TYPE,
			'numberposts' => ILM_Guide::POST_LIMIT, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
		]
	);
	?>

<?php
foreach ( $tools as $tool ) {
	$tool_link  = get_post_meta( $tool->ID, ILM_Guide::TOOL_URL_META_KEY, true );
	$link_class = pathinfo( $tool_link, PATHINFO_EXTENSION ) === 'pdf' ? 'pdf' : 'link';
	$tags       = wp_get_post_terms( $tool->ID, ILM_Guide::TAG_TAXONOMY );
	?>
		<section class='tool'>
			<span class="<?php echo esc_attr( $link_class ); ?>"><?php echo esc_html( $link_class ); ?></span>
			<h3><a href="<?php echo esc_url( $tool_link ); ?>"><?php echo esc_html( $tool->post_title ); ?></a></h3>
			<?php	foreach ( $tags as $ilm_tag ) : ?>
				<span class="tag"><?php echo esc_html( $ilm_tag->name ); ?></span>
			<?php endforeach; ?>
		<?php echo wp_kses_post( $tool->post_content ); ?>
		</section>
		<?php
}
?>
