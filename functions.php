<?php
/**
 * Terraso functions and definitions.
 *
 * @package Terraso
 * @since   1.0.0
 */

define( 'THEME_VERSION', '1.0.1' );

/**
 * Enqueue frontend scripts and styles.
 */
function zakra_child_enqueue_styles() {

	$parent_style = 'zakra-style'; // Parent theme style handle 'zakra-style'.

	wp_enqueue_style(
		$parent_style,
		get_template_directory_uri() . '/style.css',
		[],
		THEME_VERSION
	);
	wp_enqueue_style(
		'zakra_child_style',
		get_stylesheet_directory_uri() . '/assets/css/main.min.css',
		[ $parent_style ],
		THEME_VERSION
	);

}

/**
 * Remove font awesome CSS.
 */
function remove_fontawesome() {
	wp_dequeue_style( 'font-awesome' );
	wp_dequeue_style( 'font-awesome-5-all' );
	wp_dequeue_style( 'font-awesome-4-shim' );
}

/**
 * Capability needed to view Ultimate Maps.
 * Granted to editors and administrators.
 */
function terraso_ultimate_maps_cap() {
	return 'terraso_ultimate_maps_cap';
}

/**
 * Add height and width to header logo.
 *
 * @param array $custom_logo_attr Custom logo image attributes.
 * @param int   $custom_logo_id   Custom logo attachment ID.
 * @param int   $blog_id          ID of the blog to get the custom logo for.
 * @return array
 */
function terraso_get_custom_logo_image_attributes( $custom_logo_attr, $custom_logo_id, $blog_id ) {
	$custom_logo_attr['width']  = 145;
	$custom_logo_attr['height'] = 41;

	return $custom_logo_attr;
}

/**
 * Remove width=1 height=1 from images.
 *
 * @param string     $html           HTML img element or empty string on failure.
 * @param id         $attachment_id  Image attachment ID.
 * @param string|int $size           Requested image size.
 * @param bool       $icon           Whether the image should be treated as an icon.
 * @param string     $attr           Array of attribute values for the image markup, keyed by attribute name.
 */
function terraso_wp_get_attachment_image( $html, $attachment_id, $size, $icon, $attr ) {
	if ( false !== strpos( $html, ' width="1" height="1"' ) && 'custom-logo' === $attr['class'] && ! empty( $attr['height'] ) && ! empty( $attr['width'] ) ) {
		$html = str_replace( ' width="1" height="1"', '', $html );
		return $html;
	}

	return $html;
}

/**
 * Add Aria label to Zakra search field.
 */
function terraso_zakra_header_search_icon_data_attrs() {
	return 'aria-label="' . esc_attr( 'Search' ) . '"';
}

/**
 * Add <meta> tag for blog entries.
 * Use the first 160 characters of the excerpt..
 */
function terraso_meta_tags() {
	if ( is_single() ) {
		$description = get_the_excerpt();
		if ( $description && count( $description ) > 160 ) {
			$description = substr( $description, 0, 159 ) . '…';
		}

		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '" />';
		}
	}
}


add_action( 'wp_enqueue_scripts', 'zakra_child_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'remove_fontawesome', 20 );
add_filter( 'ums_adminMenuAccessCap', 'terraso_ultimate_maps_cap' );
add_filter( 'get_custom_logo_image_attributes', 'terraso_get_custom_logo_image_attributes', 10, 3 );
add_filter( 'wp_get_attachment_image', 'terraso_wp_get_attachment_image', 10, 5 );
add_filter( 'zakra_header_search_icon_data_attrs', 'terraso_zakra_header_search_icon_data_attrs' );
add_action( 'wp_head', 'terraso_meta_tags' );
