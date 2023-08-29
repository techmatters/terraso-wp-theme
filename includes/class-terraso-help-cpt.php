<?php
/**
 * Help Pages CPT for Terraso
 *
 * @package Terraso
 */

/**
 * Holds methods for Help Custom Post Type
 * Class Terraso_Help_CPT
 */
class Terraso_Help_CPT {

	const CPT_SLUG = 'help';

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
	}

	/**
	 * Register Help page post type.
	 */
	public static function register_post_type() {
		$labels = [
			'name'          => esc_html__( 'Help pages', 'terraso' ),
			'singular_name' => esc_html__( 'Help page', 'terraso' ),
		];

		$language_option = get_option( 'WPLANG' );
		$language        = $language_option ? $language_option : 'en_US';
		$slug_map        = [
			'en_US' => 'help',
			'es_ES' => 'ayuda',
			'fr_FR' => 'aide',
			'pt_PT' => 'ajuda',
		];

		$localized_slug = $slug_map[ $language ];
		$slug           = $localized_slug ? $localized_slug : 'help';

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
				'slug'       => $slug,
				'with_front' => true,
			],
			'query_var'             => true,
			'supports'              => [ 'title', 'editor', 'thumbnail', 'revisions' ],
			'show_in_graphql'       => false,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-editor-help',
		];

		register_post_type( self::CPT_SLUG, $args ); // phpcs:ignore WordPress.NamingConventions.ValidPostTypeSlug.NotStringLiteral
	}
}

add_action( 'after_setup_theme', [ 'Terraso_Help_CPT', 'hooks' ] );
