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
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'zakra_child_enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'remove_fontawesome' ], 20 );
		add_filter( 'get_custom_logo_image_attributes', [ __CLASS__, 'get_custom_logo_image_attributes' ], 10, 3 );
		add_filter( 'zakra_header_search_icon_data_attrs', [ __CLASS__, 'zakra_header_search_icon_data_attrs' ] );
		add_action( 'wp_head', [ __CLASS__, 'meta_tags' ] );
		add_action( 'after_setup_theme', [ __CLASS__, 'setup' ] );
		add_filter( 'document_title_separator', [ __CLASS__, 'document_title_separator' ] );
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_action( 'init', [ __CLASS__, 'help_rewrite' ] );
		add_filter( 'auto_update_translation', '__return_true' );
		add_filter( 'body_class', [ __CLASS__, 'filter_body_class' ] );
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
	 * Register Help page post type.
	 */
	public static function register_post_type() {
		$labels = [
			'name'          => esc_html__( 'Help pages', 'terraso' ),
			'singular_name' => esc_html__( 'Help page', 'terraso' ),
		];

		$args = [
			'label'                 => esc_html__( 'Help pages', 'terraso' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_rest'          => true,
			'rest_base'             => '',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rest_namespace'        => 'wp/v2',
			'has_archive'           => false,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'delete_with_user'      => false,
			'exclude_from_search'   => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'hierarchical'          => false,
			'can_export'            => false,
			'rewrite'               => [
				'slug'       => 'help',
				'with_front' => true,
			],
			'query_var'             => true,
			'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions' ],
			'show_in_graphql'       => false,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-editor-help',
		];

		register_post_type( 'help', $args );
	}

	/**
	 * Treat /help/ as an index page.
	 */
	public static function help_rewrite() {
		add_rewrite_rule( '^help$', 'index.php?help=help', 'top' );
		add_rewrite_rule( '^ayuda$', 'index.php?help=ayuda', 'top' );
	}

	/**
	 * Adds the post name slug to the body class list.
	 *
	 * @param array $classes   List of CSS classes.
	 */
	public static function filter_body_class( $classes ) {
		$queried_obj = get_queried_object();

		if ( isset( $queried_obj->post_name ) && is_string( $queried_obj->post_name ) ) {
			$classes[] = 'slug-' . $queried_obj->post_name;
		}

		return $classes;
	}
}

add_action( 'after_setup_theme', [ 'Terraso', 'hooks' ] );
