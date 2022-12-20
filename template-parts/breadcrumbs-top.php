<?php
/**
 * ILM Guide breadcrumbs.
 *
 * @package Terraso
 */

$ilm_post_type = ILM_Guide::get_post_type();
$parent_id     = wp_get_post_parent_id();
?>
<nav class="breadcrumbs">
	<ul>
		<li><a href="/">Home</a></li>
		<li><a href="/guide/"><?php echo esc_html__( 'ILM Practical Guide' ); ?></a></li>
		<?php if ( 'ilm-element' === $ilm_post_type ) : ?>
			<li><?php echo esc_html( get_the_title( $parent_id ) ); ?></li>
		<?php elseif ( 'ilm-output' === $ilm_post_type ) : ?>
			<li class='ilm-element-name'><a href="<?php echo esc_url( get_the_permalink( $parent_id ) ); ?>"><?php echo esc_html( get_the_title( $parent_id ) ); ?></a></li>
			<li class='ilm-output-name'><?php echo esc_html( get_the_title() ); ?></li>
		<?php endif; ?>
	</ul>
</nav>
