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
	const POST_TYPE     = 'guide';
	const TAG_TAXONOMY  = 'ilm_tag';
	const TYPE_TAXONOMY = 'ilm_type';
	const POST_LIMIT    = 90;

	/**
	 * Add actions and filters.
	 */
	public static function hooks() {
		add_action( 'add_meta_boxes', [ 'ILM_Guide', 'add_meta_boxes' ] );
		add_filter( 'the_content', [ 'ILM_Guide', 'the_content' ] );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_boxes() {
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
	 * Filter to append ILM Guide output and tool content.
	 *
	 * @param string $content           Post content HTML.
	 */
	public static function the_content( $content ) {
		$post_terms = wp_get_post_terms( get_the_ID(), self::TYPE_TAXONOMY, [ 'fields' => 'slugs' ] );
		if ( $post_terms && is_array( $post_terms ) ) {
			$post_type = $post_terms[0];
		}

		if ( 'ilm-element' === $post_type ) {
			ob_start();
			get_template_part(
				'template-parts/output',
				'list',
				[
					'id'            => get_the_ID(),
					'element_title' => get_the_title(),
				]
			);
			$output_html = ob_get_clean();

			$content .= $output_html;
		} elseif ( 'ilm-output' === $post_type ) {
			$content .= ' OUTPUT ';

		}

		return $content;
	}

}

add_action( 'after_setup_theme', [ 'ILM_Guide', 'hooks' ] );
