<?php
/**
 * ILM Guide utilities for Terraso
 *
 * @package Terraso
 */

/**
 * Holds methods for ILM Guide content.
 * Class ILM_Guide
 */
class ILM_Guide {
	const POST_TYPE         = 'guide';
	const TAG_TAXONOMY      = 'ilm_tag';
	const TYPE_TAXONOMY     = 'ilm_type';
	const TOOL_URL_META_KEY = 'ilm_url';
	const POST_LIMIT        = 90;

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'zakra_after_single_post_content', [ __CLASS__, 'zakra_after_single_post_content' ] );
	}

	/**
	 * Add actions and filters.
	 */
	public static function late_hooks() {
		if ( 'guide' === get_post_type() ) {
			add_filter( 'body_class', [ __CLASS__, 'filter_body_class' ] );
			add_filter( 'get_post_metadata', [ __CLASS__, 'disable_zakra_header' ], 10, 5 );
			add_filter( 'zakra_current_layout', [ __CLASS__, 'zakra_current_layout' ] );
			add_filter( 'the_content', [ __CLASS__, 'the_content' ] );
			remove_action( 'zakra_after_single_post_content', 'zakra_post_navigation', 10 );
		}
	}

	/**
	 * Print breadcrumbs.
	 */
	public static function get_breadcrumbs() {
		ob_start();

		get_template_part(
			'template-parts/breadcrumbs',
			'top',
			[
				'id'    => get_the_ID(),
				'title' => get_the_title(),
			]
		);

		echo wp_kses_post( ob_get_clean() );
	public static function get_post_type() {
		$post_terms = wp_get_post_terms( get_the_ID(), self::TYPE_TAXONOMY, [ 'fields' => 'slugs' ] );
		if ( $post_terms && is_array( $post_terms ) ) {
			return $post_terms[0];
		}
	}

	}

	/**
	 * Adds the post name slug to the body class list.
	 *
	 * @param array $classes   List of CSS classes.
	 */
	public static function filter_body_class( $classes ) {
		$queried_obj = get_queried_object();

		if ( isset( $queried_obj->post_name ) && is_string( $queried_obj->post_name ) ) {
			$classes[] = 'guide-' . $queried_obj->post_name;
		}

		return $classes;
	}

	/**
	 * Disable the header (which is outside of .entry-content) so we can add our own header.
	 *
	 * @param mixed  $value     The value to return, either a single metadata value or an array
	 *                          of values depending on the value of `$single`. Default null.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key  Metadata key.
	 * @param bool   $single    Whether to return only the first value of the specified `$meta_key`.
	 * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
	 *                          or any other object type with an associated meta table.
	 */
	public static function disable_zakra_header( $value, $object_id, $meta_key, $single, $meta_type ) {
		if ( 'zakra_page_header' === $meta_key ) {
			return '0';
		}

		return $value;
	}

	/**
	 * Adds the meta box container to Guide CPT posts.
	 */
	public static function add_meta_boxes() {
		add_meta_box( 'zakra-page-setting', esc_html__( 'Page Settings', 'zakra' ), 'Zakra_Meta_Box_Page_Settings::render', [ 'guide' ] );
	}

	/**
	 * Get the first paragraph of post content.
	 *
	 * @param string $content           Post content HTML.
	 */
	public static function get_first_paragraph( $content ) {
		if ( ! has_blocks( $content ) ) {
			return;
		}

		$blocks = parse_blocks( $content );
		return $blocks[0]['innerHTML'];
	}

	/**
	 * For ILM Guide, set layout to stretched to hide sidebar and allow for customization.
	 *
	 * @param string $layout           Zakra layout name.
	 */
	public static function zakra_current_layout( $layout ) {
		if ( 'guide' === get_post_type() ) {
			return 'tg-site-layout--stretched';
		}

		return $layout;
	}

	/**
	 * Append ILM Guide output and tool content.
	 */
	public static function zakra_after_single_post_content() {
		$post_terms = wp_get_post_terms( get_the_ID(), self::TYPE_TAXONOMY, [ 'fields' => 'slugs' ] );
		if ( $post_terms && is_array( $post_terms ) ) {
			$post_type = $post_terms[0];
		}

		ob_start();
		if ( 'ilm-element' === $post_type ) {
			get_template_part(
				'template-parts/output',
				'list',
				[
					'id'    => get_the_ID(),
					'title' => get_the_title(),
				]
			);
		} elseif ( 'ilm-output' === $post_type ) {
			get_template_part(
				'template-parts/element',
				'list',
				[
					'id'    => get_the_ID(),
					'title' => get_the_title(),
				]
			);
		}

		echo wp_kses_post( ob_get_clean() );
	}

	/**
	 * Prepend title to ILM content.
	 *
	 * @param string $content           Post content HTML.
	 */
	public static function the_content( $content ) {
		return self::get_breadcrumbs() . '<h1>' . esc_html( get_the_title() ) . '</h1>' . $content;
	}

}

add_action( 'after_setup_theme', [ 'ILM_Guide', 'hooks' ] );
add_action( 'wp', [ 'ILM_Guide', 'late_hooks' ] );
