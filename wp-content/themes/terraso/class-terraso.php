<?php
/**
 * Terraso site core functions.
 *
 * @package Terraso
 * @since   1.0.0
 */

/**
 * Holds methods for Terraso site.
 * Class Terraso
 */
class Terraso {

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'remove_meta_boxes' ] );
		add_action( 'after_switch_theme', [ __CLASS__, 'add_ppma_capabilities' ] );
		add_action( 'after_setup_theme', [ __CLASS__, 'setup' ] );
		add_action( 'init', [ __CLASS__, 'help_rewrite' ] );
		add_action( 'init', [ __CLASS__, 'kses_allow_additional_tags' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'remove_fontawesome' ], 20 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'zakra_child_enqueue_styles' ], 9 );
		add_action( 'wp_head', [ __CLASS__, 'meta_tags' ] );
		add_action( 'zakra_action_footer_bottom_bar_one', [ __CLASS__, 'zakra_action_footer_bottom_bar_one' ] );
		add_filter( 'body_class', [ __CLASS__, 'filter_body_class' ] );
		add_filter( 'document_title_separator', [ __CLASS__, 'document_title_separator' ] );
		add_filter( 'get_custom_logo_image_attributes', [ __CLASS__, 'get_custom_logo_image_attributes' ], 10, 3 );
		add_filter( 'jetpack_open_graph_image_default', [ __CLASS__, 'jetpack_open_graph_image_default' ] );
		add_filter( 'zakra_header_search_icon_data_attrs', [ __CLASS__, 'zakra_header_search_icon_data_attrs' ] );
		add_filter( 'get_search_form', [ __CLASS__, 'zakra_search_placeholder' ], 10, 2 );
		add_filter( 'zakra_current_layout', [ __CLASS__, 'zakra_current_layout' ] );

		// Automatic update-related filters. Update silently.
		add_filter( 'auto_update_translation', '__return_true' );
		add_filter( 'auto_theme_update_send_email', '__return_false' );
		add_filter( 'auto_plugin_update_send_email', '__return_false' );
	}

	/**
	 * Add actions and filters.
	 */
	public static function late_hooks() {

		// Hide post navigation on help pages.
		if ( get_post_type() === Terraso_Help_CPT::CPT_SLUG ) {
			remove_action( 'zakra_after_single_post_content', 'zakra_post_navigation' );
		}
	}

	/**
	 * Allow editors to manage PublishPress Authors settings.
	 */
	public static function add_ppma_capabilities() {
		$role = get_role( 'editor' );
		$role->add_cap( 'ppma_manage_authors', true );
		$role->add_cap( 'ppma_edit_post_authors', true );
	}

	/**
	 * Remove Zakra Page Settings box from blog posts.
	 */
	public static function remove_meta_boxes() {
		remove_meta_box( 'zakra-page-setting', 'post', 'advanced' );
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public static function zakra_child_enqueue_styles() {

		$parent_style = 'zakra-style'; // Parent theme style handle 'zakra-style'.

		wp_enqueue_style(
			$parent_style,
			get_template_directory_uri() . '/style.css',
			[],
			THEME_VERSION
		);

		$ext = defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ? 'src' : 'min';

		wp_enqueue_style(
			'zakra_child_style',
			get_stylesheet_directory_uri() . "/assets/css/main.${ext}.css",
			[ $parent_style ],
			THEME_VERSION
		);
	}

	/**
	 * Remove font awesome CSS.
	 */
	public static function remove_fontawesome() {
		wp_dequeue_style( 'font-awesome' );
		wp_dequeue_style( 'font-awesome-5-all' );
		wp_dequeue_style( 'font-awesome-4-shim' );
	}

	/**
	 * Add height and width to header logo.
	 *
	 * @param array $custom_logo_attr Custom logo image attributes.
	 * @param int   $custom_logo_id   Custom logo attachment ID.
	 * @param int   $blog_id          ID of the blog to get the custom logo for.
	 * @return array
	 */
	public static function get_custom_logo_image_attributes( $custom_logo_attr, $custom_logo_id, $blog_id ) {
		$custom_logo_attr['width']  = 145;
		$custom_logo_attr['height'] = 41;

		return $custom_logo_attr;
	}

	/**
	 * Add Aria label to Zakra search field.
	 *
	 * @return string
	 */
	public static function zakra_header_search_icon_data_attrs() {
		return 'aria-label="' . esc_attr( 'Search' ) . '"';
	}

	/**
	 * Add Aria label to Zakra search field.
	 *
	 * @param string $form The search form HTML output.
	 * @param array  $args The array of arguments for building the search form.
	 *                     See get_search_form() for information on accepted arguments.
	 */
	public static function zakra_search_placeholder( $form, $args ) {
		return str_replace( 'Type &amp; hit Enter &hellip;', 'Search &hellip;', $form );
	}

	/**
	 * Add <meta> tag for blog entries.
	 * Use the first 160 characters of the excerpt..
	 */
	public static function meta_tags() {
		if ( is_single() ) {
			$description = get_the_excerpt();
			if ( strlen( $description ) > 160 ) {
				$description = substr( $description, 0, 159 ) . '…';
			}

			if ( $description ) {
				echo "<meta name='description' content='" . esc_attr( $description ) . "' />\n";
			}
		}
	}

	/**
	 * Setup tasks.
	 * - load translations.
	 */
	public static function setup() {
		load_theme_textdomain( 'terraso', get_stylesheet_directory() . '/languages' );
	}

	/**
	 * Use vertical bar for separator.
	 *
	 * @return string
	 */
	public static function document_title_separator() {
		return '|';
	}

	/**
	 * Treat /help/ as an index page.
	 */
	public static function help_rewrite() {
		add_rewrite_rule( '^help$', 'index.php?help=help', 'top' );
		add_rewrite_rule( '^ayuda$', 'index.php?help=ayuda', 'top' ); // es.
		add_rewrite_rule( '^aide$', 'index.php?help=aide', 'top' );   // fr.
		add_rewrite_rule( '^ajuda$', 'index.php?help=ajuda', 'top' ); // pt.
	}

	/**
	 * Adds the post name slug to the body class list.
	 *
	 * @param array $classes   List of CSS classes.
	 *
	 * @return array
	 */
	public static function filter_body_class( $classes ) {
		$queried_obj = get_queried_object();

		if ( isset( $queried_obj->post_name ) && is_string( $queried_obj->post_name ) ) {
			$classes[] = 'slug-' . $queried_obj->post_name;
		}

		return $classes;
	}

	/**
	 * Fallback image for open graph tags
	 */
	public static function jetpack_open_graph_image_default() {
		return get_stylesheet_directory_uri() . '/assets/img/terraso.png';
	}

	/**
	 * Copyright message
	 */
	public static function zakra_action_footer_bottom_bar_one() {
		echo wp_kses_post( '© ' . esc_html( gmdate( 'Y' ) ) . " <a href='https://techmatters.org/'>Tech Matters</a>." );
	}

	/**
	 * Allow additional tags and attributes.
	 */
	public static function kses_allow_additional_tags() {
		global $allowedposttags;

		$style_attributes = [
			'class' => true,
			'id'    => true,
			'style' => true,
		];

		$allowed_tags_data = [
			'iframe' => array_merge(
				$style_attributes,
				[
					'height'         => true,
					'loading'        => true,
					'name'           => true,
					'referrerpolicy' => true,
					'sandbox'        => true,
					'src'            => true,
					'srcdoc'         => true,
					'title'          => true,
					'width'          => true,
				]
			),
		];


		foreach ( $allowed_tags_data as $tag => $new_attributes ) {
			if ( ! isset( $allowedposttags[ $tag ] ) || ! is_array( $allowedposttags[ $tag ] ) ) {
				$allowedposttags[ $tag ] = []; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			$allowedposttags[ $tag ] = array_merge( $allowedposttags[ $tag ], $new_attributes ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	/**
	 * Hides the sidebar on help pages.
	 *
	 * @param string $layout   Current Zakra page layout.
	 *
	 * @return string
	 */
	public static function zakra_current_layout( $layout ) {
		if ( get_post_type() === Terraso_Help_CPT::CPT_SLUG ) {
			return 'zak-site-layout--contained';
		}

		return $layout;
	}
}

add_action( 'after_setup_theme', [ 'Terraso', 'hooks' ] );
add_action( 'wp', [ 'Terraso', 'late_hooks' ] );
