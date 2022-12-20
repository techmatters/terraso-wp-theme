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
	 * Subset of SVG tags/attributes we use.
	 *
	 * @var $svg_tags
	 */
	public static $svg_tags = [
		'svg'  => [ 'class', 'id', 'style', 'width', 'height', 'fill', 'xmlns', 'xmlns:xlink', 'xmlns:serif', 'xml:space', 'viewbox' ], // viewbox must be lowercase.
		'rect' => [ 'class', 'id', 'style', 'width', 'height', 'fill', 'rx', 'x', 'y' ],
		'path' => [ 'class', 'id', 'style', 'fill', 'd', 'stroke', 'stroke-linecap', 'stroke-linejoin', 'stroke-width' ],
		'g'    => [ 'class', 'id', 'style', 'transform' ],
	];

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'zakra_after_single_post_content', [ __CLASS__, 'zakra_after_single_post_content' ] );
		add_action( 'init', [ __CLASS__, 'guide_rewrite' ] );
		add_action( 'init', [ __CLASS__, 'allow_svg_tags' ] );
		add_filter( 'safe_style_css', [ __CLASS__, 'allow_svg_css' ] );
		add_action( 'pre_get_posts', [ __CLASS__, 'guide_rewrite' ] );
		add_filter( 'manage_guide_posts_columns', [ __CLASS__, 'guide_admin_columns' ] );
		add_action( 'manage_guide_posts_custom_column', [ __CLASS__, 'guide_type_column_content' ], 10, 2 );
	}

	/**
	 * Add the date ILM Type column.
	 *
	 * @param array $defaults default settings for the columns.
	 */
	public static function guide_admin_columns( $defaults ) {
		unset( $defaults['date'] );
		$defaults['ilm_type'] = __( 'Type' );
		$defaults['date']     = __( 'Date' );

		return $defaults;
	}

	/**
	 * Add column content for ILM Type column.
	 *
	 * @param string  $column_name  Column name.
	 * @param integer $post_id     Post ID.
	 */
	public static function guide_type_column_content( $column_name, $post_id ) {
		if ( 'ilm_type' === $column_name ) {
			$post_type = substr( self::get_post_type( $post_id ), 4 );
			echo esc_html( ucwords( $post_type ) );
		}
	}

	/**
	 * Add actions and filters.
	 */
	public static function late_hooks() {
		if ( 'guide' === get_post_type() ) {

			if ( 'ilm-output' === self::get_post_type() ) {
				$ext = defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG ? 'src' : 'min';

				wp_enqueue_script( 'plausible-analytics-terraso', get_stylesheet_directory_uri() . "/assets/js/plausible.${ext}.js", [], THEME_VERSION, false );
			}

			// redirect tools pages to corresponding outputs.
			if ( 'ilm-tool' === self::get_post_type() ) {
				wp_safe_redirect( get_the_permalink( wp_get_post_parent_id() ) );
				exit();
			}

			add_filter( 'body_class', [ __CLASS__, 'filter_body_class' ] );
			add_filter( 'get_post_metadata', [ __CLASS__, 'disable_zakra_header' ], 10, 5 );
			add_filter( 'zakra_current_layout', [ __CLASS__, 'zakra_current_layout' ] );
			add_filter( 'the_content', [ __CLASS__, 'the_content' ] );
			remove_action( 'zakra_after_single_post_content', 'zakra_post_navigation', 10 );
		}
	}

	/**
	 * Treat /guide/ as an index page.
	 */
	public static function guide_rewrite() {
		add_rewrite_rule( '^guide$', 'index.php?guide=intro', 'top' );
	}

	/**
	 * Allow SVG tags within WordPress
	 */
	public static function allow_svg_tags() {
		global $allowedposttags;

		foreach ( self::$svg_tags as $tag => $attributes ) {
			$allowedposttags[ $tag ] = []; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			foreach ( $attributes as $a ) {
				$allowedposttags[ $tag ][ $a ] = true; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}
		}
	}

	/**
	 * Allow inline styles needed for SVGs.
	 *
	 * @param array $styles   List of allowed style rules.
	 * @return array
	 */
	public static function allow_svg_css( $styles ) {
		$styles[] = 'fill';
		$styles[] = 'fill-rule';
		$styles[] = 'clip-rule';
		$styles[] = 'stroke-linejoin';
		$styles[] = 'stroke-miterlimit';

		return $styles;
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

		return ob_get_clean();
	}

	/**
	 * Gets ILM post type (output, element, tool, guide)
	 *
	 * @param integer $id      Post ID.
	 */
	public static function get_post_type( $id = null ) {
		if ( ! $id ) {
			$id = get_the_ID();
		}
		$post_terms = wp_get_post_terms( $id, self::TYPE_TAXONOMY, [ 'fields' => 'slugs' ] );
		if ( $post_terms && is_array( $post_terms ) ) {
			return $post_terms[0];
		}

		return 'ilm-' . self::POST_TYPE;
	}

	/**
	 * Gets HTML for ILM Guide section image (one of 5 elements).
	 */
	public static function get_section_image() {
		$post_type = self::get_post_type();

		if ( 'ilm-output' === $post_type ) {
			$slug = get_post_field( 'post_name', wp_get_post_parent_id() );
		} elseif ( 'ilm-element' === $post_type ) {
			$slug = get_post_field( 'post_name' );
		} else {
			return;
		}

		$svg = file_get_contents( get_stylesheet_directory() . '/assets/images/ilm/' . $slug . '.svg' );
		return '<span class="' . esc_attr( 'section-image ' . $slug ) . '">' . $svg . '</span>';
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

		$post_type = self::get_post_type();
		$classes[] = 'guide-' . ( $post_type ?? 'intro' );
		if ( 'ilm-output' === $post_type ) {
			$parent_name = get_post_field( 'post_name', wp_get_post_parent_id() );
			$classes[]   = 'parent-' . $parent_name;
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
		$post_type = self::get_post_type();

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
				'template-parts/tool',
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
		$result = '';
		if ( in_array( self::get_post_type(), [ 'ilm-element', 'ilm-output' ], true ) ) {
			$result = self::get_breadcrumbs() . '<h1>' . self::get_section_image() . esc_html( get_the_title() ) . '</h1>';
		}
		return $result . $content;
	}

}

add_action( 'after_setup_theme', [ 'ILM_Guide', 'hooks' ] );
add_action( 'wp', [ 'ILM_Guide', 'late_hooks' ] );
