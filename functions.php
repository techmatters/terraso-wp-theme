<?php
/*
 * Child theme functions file
 *
 */
function zakra_child_enqueue_styles() {

	$parent_style = 'zakra-style'; // parent theme style handle 'zakra-style'

	// Enqueue parent and chid theme style.css
	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'zakra_child_style',
		get_stylesheet_directory_uri() . '/assets/css/main.min.css',
		[ $parent_style ],
		'1.0.1'
	);

}

function remove_fontawesome() {
	wp_dequeue_style( 'font-awesome' );
	wp_dequeue_style( 'font-awesome-5-all' );
	wp_dequeue_style( 'font-awesome-4-shim' );
}

function terraso_ultimate_maps_cap() {
	return 'terraso_ultimate_maps_cap';
}

// add height and width to header logo
function terraso_get_custom_logo_image_attributes( $custom_logo_attr, $custom_logo_id, $blog_id ) {
	$custom_logo_attr['width'] = 145;
	$custom_logo_attr['height'] = 41;

	return $custom_logo_attr;
}

function terraso_wp_get_attachment_image( $html, $attachment_id, $size, $icon, $attr ) {
	if ( false !== strpos( $html, ' width="1" height="1"' ) && 'custom-logo' === $attr['class'] && ! empty( $attr['height'] ) && ! empty( $attr['width'] ) ) {
		$html = str_replace( ' width="1" height="1"', '', $html );
		return $h5ml;
	}

	return $html;
}

function terraso_zakra_header_search_icon_data_attrs() {
	return 'aria-label="' . esc_attr( 'Search' ) . '"';
}

function terraso_meta_tags() {
	if ( is_single() ) {
		$description = get_the_excerpt();
		if ( sizeof( $description ) > 160 ) {
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
