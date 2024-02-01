<?php
/**
 * WPCLI Utilities for Terraso
 *
 * @package Terraso
 */

/**
 * Holds methods for WP_CLI command related to Terraso
 * Class Terraso_CLI
 */
class Terraso_CLI extends WP_CLI_Command { // phpcs:ignore WordPressVIPMinimum.Classes.RestrictedExtendClasses.wp_cli,Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

	const POST_TYPE     = 'guide';
	const TAG_TAXONOMY  = 'ilm_tag';
	const TYPE_TAXONOMY = 'ilm_type';

	/**
	 * Insert the outputs in to the database.
	 *
	 * @param string $parent_title      The ILM element (output) these outputs (tools) belong to.
	 * @param array  $data              Array of element titles and contents.
	 * @param string $type              Item type (output or tool).
	 * @param bool   $dry_run           If performing a try run.
	 */
	public static function import_data( $parent_title, $data, $type, $dry_run ) {

		$query = new WP_Query(
			[
				'post_type'              => self::POST_TYPE,
				'title'                  => $parent_title,
				'post_status'            => 'all',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			]
		);

		if ( ! empty( $query->post ) ) {
			$post_parent = $query->post;
		} else {
			$post_parent = null;
		}


		if ( ! $post_parent ) {
			WP_CLI::error( "Could not find post for parent item {$parent_title}" );
		}

		foreach ( $data as $item ) {
			WP_CLI::line( "Creating item {$item['title']}" );

			$post_data = [
				'post_title'   => $item['title'],
				'post_content' => $item['contents'],
				'post_status'  => 'publish',
				'post_type'    => self::POST_TYPE,
				'post_parent'  => $post_parent->ID,
			];

			if ( 'tool' === $type ) {
				$post_data['meta_input'] = [
					'ilm_url' => $item['url'],
				];
			}

			if ( $dry_run ) {
				WP_CLI::line( wp_json_encode( $post_data ) );
			} else {
				$result = wp_insert_post( $post_data );
				if ( is_wp_error( $result ) ) {
					WP_CLI::error( "Failed to create output. Error: {$result->get_error_message()}" );
				} else {
					// In CLI, terms can only be added after post is created.
					// See https://wordpress.stackexchange.com/a/210233/8591.
					wp_set_post_terms( $result, 'ilm-' . $type, self::TYPE_TAXONOMY );

					if ( 'tool' === $type ) {
						wp_set_post_terms( $result, explode( ',', $item['tags'] ), self::TAG_TAXONOMY );
					}
				}
			}
		}
	}

	/**
	 * Convert paragraphs to Guenberg <p> blocks.
	 *
	 * @param string $contents           Input text.
	 */
	public static function make_p_blocks( $contents ) {
		$paragraphs         = explode( "\n\n", $contents );
		$wrapped_paragraphs = array_map(
			function ( $text ) {
				return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->";
			},
			$paragraphs
		);

		return implode( "\n\n", $wrapped_paragraphs );
	}


	/**
	 * Read in CSV data and return an array.
	 *
	 * @param string $csv_file           Path to a CSV file.
	 * @param string $type               Type of item to parts (output or tool).
	 */
	public static function get_csv_data( $csv_file, $type ) {
		if ( ! $csv_file ) {
			return;
		}

		$handle = fopen( $csv_file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( false === $handle ) {
			return;
		}

		$result  = [];
		$counter = 0;
		do {
			$data = fgetcsv( $handle );

			// Skip header row from CSV.
			if ( 0 === $counter++ ) {
				continue;
			}

			if ( 'output' === $type ) {
				list( $title, $contents, $parent ) = $data;
			} elseif ( 'tool' === $type ) {
				list( $title, $parent, $contents, $url, $tags ) = $data;
			}

			if ( ! $parent ) {
				continue;
			}

			if ( ! isset( $result[ $parent ] ) ) {
				$result[ $parent ] = [];
			}
			if ( $title && $contents ) {
				$item = [
					'title'    => trim( $title ),
					'contents' => trim( self::make_p_blocks( $contents ) ),
				];
				if ( ! empty( $url ) ) {
					$item['url'] = $url;
				}
				if ( ! empty( $tags ) ) {
					$item['tags'] = $tags;
				}
				$result[ $parent ][] = $item;
			}
		} while ( false !== $data );

		fclose( $handle );

		return $result;
	}

	/**
	 * Create tags.
	 *
	 * ## EXAMPLES
	 *
	 *   wp terraso create-ilm-tags
	 *   wp terraso create-ilm-tags --dry-run
	 *
	 * @synposis create-ilm-tags --dry-run
	 *
	 * @subcommand create-ilm-tags
	 * @param array $args           CLI arguments.
	 * @param array $assoc_args     CLI arguments, associative array.
	 */
	public function create_ilm_tags( $args, $assoc_args ) {
		$tags = [
			'Analytical',
			'App',
			'Article',
			'Co-design',
			'Communication',
			'Guide',
			'Mapping',
			'Matchmaking',
			'Project Management',
			'Report',
			'Template',
			'Website',
			'Worksheet',
		];

		self::create_tags_with_tax( $args, $assoc_args, $tags, self::TAG_TAXONOMY );
	}

	/**
	 * Create tags.
	 *
	 * ## EXAMPLES
	 *
	 *   wp terraso create-ilm-types
	 *   wp terraso create-ilm-types --dry-run
	 *
	 * @synposis create-ilm-types --dry-run
	 *
	 * @subcommand create-ilm-types
	 * @param array $args           CLI arguments.
	 * @param array $assoc_args     CLI arguments, associative array.
	 */
	public function create_ilm_types( $args, $assoc_args ) {
		$tags = [
			'Element',
			'Output',
			'Tool',
		];

		self::create_tags_with_tax( $args, $assoc_args, $tags, self::TYPE_TAXONOMY );
	}

	/**
	 * Create tags.
	 *
	 * @param array  $args           CLI arguments.
	 * @param array  $assoc_args     CLI arguments, associative array.
	 * @param array  $tags           List of tags.
	 * @param string $taxonomy       Name of taxonomy.
	 */
	public static function create_tags_with_tax( $args, $assoc_args, $tags, $taxonomy ) {
		if ( isset( $assoc_args['dry-run'] ) && 'false' === $assoc_args['dry-run'] ) {
			$dry_run = false;
		} else {
			$dry_run = true;
			WP_CLI::line( '!!! Doing a dry-run, no posts will be created.' );
		}

		foreach ( $tags as $tag ) {
			if ( $dry_run ) {
				WP_CLI::line( "Tag: {$tag}" );
			} else {
				$result = wp_create_term( $tag, $taxonomy );
				if ( is_wp_error( $result ) ) {
					WP_CLI::warn( "Failed to create tag {$tag}. Error: {$result->get_error_message()}" );
				} else {
					WP_CLI::line( "Created tag: {$tag}" );
				}
			}
		}
	}


	/**
	 * Import ILM Guide data from CSV
	 *
	 * ## EXAMPLES
	 *
	 *   wp terraso import-ilm-data --type=<output|tool> --file=<file>
	 *   wp terraso import-ilm-data --type=<output|tool> --file=<file> --dry-run
	 *
	 * @synposis --type=<output|tool> --file=<file> --dry-run
	 *
	 * @subcommand import-ilm-data
	 * @param array $args           CLI arguments.
	 * @param array $assoc_args     CLI arguments, associative array.
	 */
	public function import_ilm_data( $args, $assoc_args ) {
		if ( empty( $assoc_args['type'] ) ) {
			WP_CLI::error( 'Specify a type of data to import.' );
		}

		if ( ! in_array( $assoc_args['type'], [ 'output', 'tool' ], true ) ) {
			WP_CLI::error( 'Specify a type of data to import.' );
		}

		if ( empty( $assoc_args['file'] ) ) {
			WP_CLI::error( 'Specify a file to import from.' );
		}

		if ( isset( $assoc_args['dry-run'] ) && 'false' === $assoc_args['dry-run'] ) {
			$dry_run = false;
		} else {
			$dry_run = true;
			WP_CLI::line( '!!! Doing a dry-run, no posts will be created.' );
		}

		$import_file = $assoc_args['file'];
		if ( ! file_exists( $import_file ) ) {
			WP_CLI::error( "File {$import_file} does not exist." );
		}

		$type = $assoc_args['type'];
		$data = self::get_csv_data( $import_file, $type );

		$item_count = 0;
		foreach ( $data as $item => $output_data ) {
			WP_CLI::line( "Found item {$item}." );
			++$item_count;

			self::import_data( trim( $item ), $output_data, $type, $dry_run );

			if ( 0 === $item_count % 100 ) {
				WP_CLI::line( 'sleeping' );
				sleep( 5 );
			}
		}
	}
}

WP_CLI::add_command( 'terraso', 'Terraso_CLI' );
